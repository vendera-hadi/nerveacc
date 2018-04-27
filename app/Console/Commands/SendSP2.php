<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\TrInvoice;

class SendSP2 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sp:sendsp2';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Eksekusi lgsg kirim email SP1 jika today = 14 + invoice duedate';

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
        $list = TrInvoice::where('inv_outstanding','>',0)->where('inv_post',1)->where('inv_iscancel',0)->whereRaw("NOW()::date =  (inv_duedate + interval '14 day')::date")->get();
        foreach ($list as $invoice) {
            // masukin invoice dalam antrean
            try{
                \Mail::to(@$invoice->MsTenant->tenan_email)
                        // ->cc($moreUsers)
                        ->send(new \App\Mail\SuratPeringatan('sp2', $invoice));
                $this->info("Sending Email to ".@$invoice->MsTenant->tenan_email);
            }catch(\Exception $e){
                // do nothing or inform to admin sending sp gagal
            }
        }
        $this->info("Done");
    }
}
