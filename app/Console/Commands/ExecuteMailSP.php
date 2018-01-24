<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Models\TrInvoice;
use App\Models\Autoreminder;
use App\Models\MsConfig;
use App\Models\MsCompany;
use App\Models\MsEmailTemplate;
use DB;

class ExecuteMailSP extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sp:mailing';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fungsi utk execute email SP ke customer';

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
        // get latest 10 email to be sent
        $list = Autoreminder::where('sent',false)->limit(1)->get();
        if($list->count() > 0){
            foreach ($list as $val) {
                // cek keterlambatan
                $highest_interval = 0;
                $outstanding_inv = $val->invoice->where('inv_outstanding','>',0);
                foreach($outstanding_inv as $inv){
                    $today = new \DateTime(date('Y-m-d'));
                    $duedate  = new \DateTime($inv->inv_duedate);
                    $interval = $duedate->diff($today)->days;
                    if(empty($highest_interval)){
                        $highest_interval = $interval;
                    }else{
                       if($interval > $highest_interval) $highest_interval = $interval;
                    }
                }
                // highest interval > 30 = SP1, > 60 = SP2
                if($highest_interval > 30) $type = 'SP1';
                if($highest_interval > 60) $type = 'SP2';
                // send mail
                $result = $this->sendMail($val->tenan_id, $type);
                if($result){
                    if($type == 'SP1') $val->sp1 = true;
                    if($type == 'SP2') $val->sp2 = true;
                    $val->sent = true;
                    $val->save();
                    $this->info("SENT");
                }else{
                    $this->info("NOT SENT");
                }
            }
        }else{
            $this->info("NOTHING TO BE SENT");
        }
    }

    private function sendMail($tenan_id, $type){
        try{
            $id = $tenan_id;
            $company = MsCompany::with('MsCashbank')->first()->toArray();
            $signature = @MsConfig::where('name','digital_signature')->first()->value;
            $signatureFlag = @MsConfig::where('name','invoice_signature_flag')->first()->value;
            $emailPengelola = @MsConfig::where('name','email_pengelola')->first()->value;
            $emailTemplate = MsEmailTemplate::where('name',$type)->first();
            $invoice_data = TrInvoice::where('tenan_id', $id)->orderBy('created_at','desc')->get();

            $set_data = array(
                'id' => $id,
                'email' => $emailPengelola,
                'invoice_data' => $invoice_data,
                'title' => $emailTemplate->title,
                'content' => $emailTemplate->content,
                'company' => $company,
                'signature' => $signature,
                'signatureFlag' => $signatureFlag
            );
            \Mail::to($invoice_data[0]->MsTenant->tenan_email)->send(new \App\Mail\CustomReminderMail($set_data));
            return true;
        }catch(\Exception $e){
            return false;
        }
    }
}
