<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Mail;

class SendMail implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    protected $mailClass;
    protected $to;
    protected $cc;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($mailClass, $to, $cc)
    {
        $this->mailClass = $mailClass;
        $this->to = $to;
        $this->cc = $cc;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Mail::to($this->to)->cc($this->cc)->send($this->mailClass);
    }
}
