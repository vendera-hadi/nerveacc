<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

use App\Models\TrInvoice;
use App\Models\MsCompany;
use App\Models\MsEmailTemplate;
use PDF;

class SuratPeringatan extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;


    protected $invoice, $type;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($type, TrInvoice $invoice)
    {
        $this->invoice = $invoice;
        $this->type = $type;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $data['invoice'] = $this->invoice;
        $tenan_id = $data['invoice']->tenan_id;
        $inv_date = $data['invoice']->inv_date;
        $data['company'] = MsCompany::with('MsCashbank')->first();
        $data['utilities_invoices'] = TrInvoice::where('tenan_id',$tenan_id)->where('inv_outstanding','>',0)->where('invtp_id',1)->where('inv_post',1)->where('inv_iscancel',0)->where('inv_date','<=',date('Y-m-t',strtotime($inv_date)))->get();

        $data['maintenance_invoices'] = TrInvoice::where('tenan_id',$tenan_id)->where('inv_outstanding','>',0)->where('invtp_id',2)->where('inv_post',1)->where('inv_iscancel',0)->where('inv_date','<=',date('Y-m-t',strtotime($inv_date)))->get();
        switch ($this->type) {
            case 'sp1':
                $data['emailtpl'] = MsEmailTemplate::where('name','SP1')->first();
                $view = 'emails.sp1';
                break;
            case 'sp2':
                $data['emailtpl'] = MsEmailTemplate::where('name','SP2')->first();
                $view = 'emails.sp2';
                break;
            case 'sp3':
                $data['emailtpl'] = MsEmailTemplate::where('name','SP3')->first();
                $view = 'emails.sp3';
                break;

            default:
                $data['emailtpl'] = MsEmailTemplate::where('name','SP1')->first();
                $view = 'emails.sp1';
                break;
        }

        $pdf = PDF::loadView($view, $data)->setPaper('a4')->output();
        return $this->view($view, $data)->subject($data['emailtpl']->title)->attachData($pdf, $data['emailtpl']->title.'.pdf', [
                        'mime' => 'application/pdf',
                    ]);
    }
}
