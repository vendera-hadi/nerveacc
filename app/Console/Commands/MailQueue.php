<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\EmailQueue;
use Mail;

class MailQueue extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mail:execute';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cron detect & kirim email';

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
        $queue = EmailQueue::where('status','new')->orderBy('created_at')->limit(1)->first();
        if(!empty($queue)){
            try{
                if(empty($queue->cc)) $queue->cc = [];
                Mail::to($queue->to)->cc($queue->cc)->send(new $queue->mailclass($queue->ref_id));
                $queue->status = 'success';
                $queue->sent_at = date('Y-m-d H:i:s');
                $queue->save();
                $this->info('Email sukses terkirim');
            }catch(\Exception $e){
                $queue->note = $e->getMessage();
                $queue->status = 'failed';
                $queue->save();
                $this->info('Terjadi error saat mengirim email');
            }
        }else{
            $this->info('Email tidak ada dalam antrean');
        }
    }
}
