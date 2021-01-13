<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

use App\Models\TrInvoice;
use App\Models\TrInvoiceDetail;
use App\Models\MsCompany;
use App\Models\ReminderH;
use App\Models\ReminderD;
use App\Models\MsEmailTemplate;
use App\Models\MsConfig;
use PDF;

class ManualReminderMail extends Mailable
{
    use Queueable, SerializesModels;


    protected $reminderHeader;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($reminderHeader)
    {
        $this->reminderHeader = ReminderH::find($reminderHeader);
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $inv_id = $this->reminderHeader->id;
        if(!is_array($inv_id)) $inv_id = [$inv_id];

        $invoice_data = ReminderH::select('reminder_header.*', 'ms_unit.unit_code','ms_email_templates.subject','ms_tenant.tenan_name')
                                    ->leftjoin('ms_unit_owner','ms_unit_owner.unit_id','=','reminder_header.unit_id')
                                    ->leftJoin('ms_unit','reminder_header.unit_id','=','ms_unit.id')
                                    ->leftjoin('ms_tenant','ms_tenant.id','=','ms_unit_owner.tenan_id')
                                    ->leftjoin('ms_email_templates','ms_email_templates.id','=','reminder_header.sp_type')
                                    ->where('ms_unit_owner.deleted_at',NULL)
                                    ->whereIn('reminder_header.id',$inv_id)->get()->toArray();
                                    
        foreach ($invoice_data as $key => $inv) {
            $result = ReminderD::select('tr_invoice.inv_number','tr_invoice.inv_outstanding','inv_amount','ms_invoice_type.invtp_name','denda_days','denda_amount')
            ->join('tr_invoice','reminder_details.inv_id',"=",'tr_invoice.id')
            ->leftJoin('ms_invoice_type','ms_invoice_type.id',"=",'tr_invoice.invtp_id')
            ->where('reminder_details.reminderh_id',$inv['id'])
            ->orderBy('inv_date','asc')
            ->get()->toArray();

            $lastreminder = ReminderH::select('reminder_date')->where('unit_id',$inv['unit_id'])->where('sp_type',4)->orderBy('reminder_date','DESC')->first();

            $invoice_data[$key]['details'] = $result;
            if(count($lastreminder) > 0){
                $invoice_data[$key]['rdate'] = $lastreminder->reminder_date;
            }else{
                $invoice_data[$key]['rdate'] = '';
            }
        }

        $company = MsCompany::with('MsCashbank')->first()->toArray();
        $signature = @MsConfig::where('name','digital_signature')->first()->value;
        $signatureFlag = @MsConfig::where('name','invoice_signature_flag')->first()->value;
        $content = MsEmailTemplate::where('name','MANUAL')->first();

        $set_data = array(
            'invoice_data' => $invoice_data,
            'result' => $result,
            'company' => $company,
            'type' => 'mail',
            'signature' => $signature,
            'signatureFlag' => $signatureFlag,
            'title' => $content->subject,
            'content' => $content->content
        );
        
        $bodymail = @MsConfig::where('name','rm_body_email')->first()->value;
        $set_body = array(
            'bodymail' => $bodymail
        );
        
        $pdf = PDF::loadView('print_manualr', $set_data)->setPaper('a4')->output();
        return $this->view('invoicebodymail', $set_body)->subject('Reminder Outstanding '.$invoice_data[0]['reminder_no'].' dari '.$company['comp_name'])->attachData($pdf, $invoice_data[0]['reminder_no'].'.pdf', [
                        'mime' => 'application/pdf',
                    ]);
    }

}
