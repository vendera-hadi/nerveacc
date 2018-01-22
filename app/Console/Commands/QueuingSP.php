<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Models\TrInvoice;
use App\Models\Autoreminder;
use DB;

class QueuingSP extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sp:queuing';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fungsi untuk mengantrikan aging invoice ke dalam table SP queue';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // kelompokin aging invoice per tenant
        $currentMonth = date('m');
        $currentYear = date('Y');
        // list tenant yg masi ngutang
        $list = TrInvoice::select('tenan_id','tenan_name','contr_id',DB::raw('COUNT(tr_invoice.id) as totalinv'))
                ->join('ms_tenant','ms_tenant.id','=','tr_invoice.tenan_id')
                ->where('inv_outstanding', '>', 0)
                ->where(DB::raw("DATE_PART('day', NOW() - inv_duedate)"), '>=', 30)
                ->groupBy('tenan_id','tenan_name','contr_id')
                ->orderBy('tenan_name')->get();
        if($list->count() > 0){
            // check periode by bulan dan tahun ini
            $logs = Autoreminder::whereIn('tenan_id',$list->pluck('tenan_id'))->where('month',$currentMonth)->where('year',$currentYear)->get();
            $willbeinserted = collect($list->pluck('tenan_id'))->diff($logs->pluck('tenan_id'));
            // yg blum ada di list difilter, nanti by tenant di loop insert
            $insert_list = $list->whereIn('tenan_id',$willbeinserted);
            $total_insert = $insert_list->count();
            if($total_insert > 0){
                foreach ($insert_list as $tenant) {
                    // insert to autoreminder
                    $log = new Autoreminder;
                    $log->tenan_id = $tenant->tenan_id;
                    $log->contract_id = $tenant->contr_id;
                    $log->month = $currentMonth;
                    $log->year = $currentYear;
                    $log->save();
                    $this->info('New tenant reminder inserted into queue');
                    $this->info($log);
                }
                $this->info("INSERTED $total_insert RECORD(s)");
                $this->info("DONE");
            }else{
                $this->info("NOTHING TO INSERT");
            }
        }else{
            $this->info("NOTHING TO INSERT");
        }
    }
}
