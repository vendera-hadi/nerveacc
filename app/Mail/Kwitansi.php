<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

use App\Models\TrInvoice;
use App\Models\TrInvoiceDetail;
use App\Models\TrContractInvoice;
use App\Models\TrInvoicePaymhdr;
use App\Models\TrInvoicePaymdtl;
use App\Models\MsCompany;
use App\Models\TrContract;
use PDF;

class Kwitansi extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    protected $paymentHeader;

    public function __construct(TrInvoicePaymhdr $paymentHeader)
    {
        $this->paymentHeader = $paymentHeader;
    }

    public function build()
    {
        $company = MsCompany::with('MsCashbank')->first()->toArray();
        // $signature = @MsConfig::where('name','digital_signature')->first()->value;
        $paymentHeader = $this->paymentHeader;
        $contract = TrContract::where('tr_contract.tenan_id',$paymentHeader->tenan_id)->first();
        $paymentDetails = TrInvoicePaymdtl::select('tr_invoice.id','tr_invoice.inv_number','tr_invoice_paymdtl.invpayd_amount')
                                ->join('tr_invoice','tr_invoice_paymdtl.inv_id','=','tr_invoice.id')
                                ->where('tr_invoice_paymdtl.invpayh_id',$this->paymentHeader->id)->get();
        $total = 0;
        if(count($paymentDetails) > 0){
            foreach ($paymentDetails as $key => $value) {
                $total += $value->invpayd_amount;
                // get detail invoice
                $temp = [];
                $invHd = TrInvoice::find($value->id);
                $inv_details = TrInvoiceDetail::where('inv_id',$value->id)->get();
                // jabarin detail dan sisipkan order
                if(count($inv_details) > 0){
                    foreach ($inv_details as $key2 => $value2) {
                        $ctrInv = TrContractInvoice::where('contr_id',$invHd->contr_id)->where('costd_id', $value2->costd_id)->first();
                        $inv_details[$key2]['order'] = !empty(@$ctrInv->order) ? $ctrInv->order : 255;
                    }
                    $inv_details = $inv_details->toArray();
                    usort($inv_details, function($a, $b) {
                        return $a['order'] - $b['order'];
                    });
                    foreach ($inv_details as $key2 => $value2) {
                        $note = explode('<br>', $value2['invdt_note']);
                        if(count($note) > 1) $temp[] = @$note[0];
                        else $temp[] = $value2['invdt_note'];
                    }
                }
                $paymentDetails[$key]->details = $temp;
            }
        }
        $terbilang = terbilang($total);

        $set_data = array(
                'company' => $company,
                // 'signature' => $signature,
                'header' => $paymentHeader,
                'details' => $paymentDetails,
                'terbilang' => $terbilang,
                'tenan' => @$contract->MsTenant->tenan_name,
                'unit' => @$contract->MsUnit->unit_code
            );

        $view = 'print_payment';
        $pdf = PDF::loadView($view, $set_data)->setPaper('a4')->output();
        return $this->view($view, $set_data)->subject("Kwitansi Invoice")->attachData($pdf, "Kwitansi Invoice.pdf", [
                        'mime' => 'application/pdf',
                    ]);
    }

}

