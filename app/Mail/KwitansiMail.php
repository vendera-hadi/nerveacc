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
use App\Models\ExcessPayment;
use App\Models\LogExcessPayment;
use App\Models\LogPaymentUsed;
use App\Models\CreditNoteH;
use App\Models\CreditNoteD;
use PDF;

class KwitansiMail extends Mailable
{
    use Queueable, SerializesModels;


    protected $paymentHeader;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($paymentHeader)
    {
        $this->paymentHeader = TrInvoicePaymhdr::find($paymentHeader);
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $company = MsCompany::with('MsCashbank')->first()->toArray();
        $paymentHeader = $this->paymentHeader;
        //$contract = TrContract::where('tr_contract.tenan_id',$paymentHeader->tenan_id)->first();
        $paymentDetails = TrInvoicePaymdtl::select('tr_invoice.id','tr_invoice.inv_number','tr_invoice_paymdtl.invpayd_amount','tr_invoice.inv_amount','tr_creditnote_dtl.credit_amount','tr_invoice.unit_id')
                                ->join('tr_invoice','tr_invoice_paymdtl.inv_id','=','tr_invoice.id')
                                ->leftJoin('tr_creditnote_dtl','tr_creditnote_dtl.inv_id','=','tr_invoice.id')
                                ->leftJoin('tr_creditnote_hdr','tr_creditnote_hdr.id','=','tr_creditnote_dtl.creditnote_hdr_id')
                                ->where('tr_invoice_paymdtl.invpayh_id',$this->paymentHeader->id)->get();
        $unit_k = $paymentDetails[0]->unit_id;
        $contract = TrContract::where('tr_contract.tenan_id',$paymentHeader->tenan_id)->where('tr_contract.unit_id',$unit_k)->first();
        $total = 0;
        $crd = 0;
        if(count($paymentDetails) > 0){
            foreach ($paymentDetails as $key => $value) {
                $total += $value->invpayd_amount;
                $temp = [];
                $invHd = TrInvoice::find($value->id);
                $inv_details = TrInvoiceDetail::where('inv_id',$value->id)->get();
                $crd_note = CreditNoteD::join('tr_creditnote_hdr','tr_creditnote_dtl.creditnote_hdr_id','=','tr_creditnote_hdr.id')
                                ->where('tr_creditnote_dtl.inv_id',$value->id)
                                ->where('creditnote_post','t')
                                ->get();
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
                if(count($crd_note) > 0){
                    foreach ($crd_note as $key3 => $value3) {
                        $crd += $value3->credit_amount;
                        $temp[] = $value3['creditnote_keterangan'];
                    }
                }
                $paymentDetails[$key]->details = $temp;
            }
        }
        $terbilang = $this->terbilang($total - $crd);
        $set_data = array(
                'company' => $company,
                // 'signature' => $signature,
                'header' => $paymentHeader,
                'details' => $paymentDetails,
                'terbilang' => $terbilang.' Rupiah',
                'tenan' => @$contract->MsTenant->tenan_name,
                'unit' => @$contract->MsUnit->unit_code,
                'type' => 'mail'
            );
        $view = 'print_payment';
        $pdf = PDF::loadView($view, $set_data)->setPaper('a4')->output();
        return $this->view($view, $set_data)->subject("Kwitansi Invoice")->attachData($pdf, "Kwitansi Invoice.pdf", [
                        'mime' => 'application/pdf',
                    ]);
    }

    public function terbilang ($angka) {
        $angka = (float)$angka;
        $bilangan = array('','Satu','Dua','Tiga','Empat','Lima','Enam','Tujuh','Delapan','Sembilan','Sepuluh','Sebelas');
        if ($angka < 12) {
            return $bilangan[$angka];
        } else if ($angka < 20) {
            return $bilangan[$angka - 10] . ' Belas';
        } else if ($angka < 100) {
            $hasil_bagi = (int)($angka / 10);
            $hasil_mod = $angka % 10;
            return trim(sprintf('%s Puluh %s', $bilangan[$hasil_bagi], $bilangan[$hasil_mod]));
        } else if ($angka < 200) { return sprintf('Seratus %s', $this->terbilang($angka - 100));
        } else if ($angka < 1000) { $hasil_bagi = (int)($angka / 100); $hasil_mod = $angka % 100; return trim(sprintf('%s Ratus %s', $bilangan[$hasil_bagi], $this->terbilang($hasil_mod)));
        } else if ($angka < 2000) { return trim(sprintf('Seribu %s', $this->terbilang($angka - 1000)));
        } else if ($angka < 1000000) { $hasil_bagi = (int)($angka / 1000); $hasil_mod = $angka % 1000; return sprintf('%s Ribu %s', $this->terbilang($hasil_bagi), $this->terbilang($hasil_mod));
        } else if ($angka < 1000000000) { $hasil_bagi = (int)($angka / 1000000); $hasil_mod = $angka % 1000000; return trim(sprintf('%s Juta %s', $this->terbilang($hasil_bagi), $this->terbilang($hasil_mod)));
        } else if ($angka < 1000000000000) { $hasil_bagi = (int)($angka / 1000000000); $hasil_mod = fmod($angka, 1000000000); return trim(sprintf('%s Milyar %s', $this->terbilang($hasil_bagi), $this->terbilang($hasil_mod)));
        } else if ($angka < 1000000000000000) { $hasil_bagi = $angka / 1000000000000; $hasil_mod = fmod($angka, 1000000000000); return trim(sprintf('%s Triliun %s', $this->terbilang($hasil_bagi), $this->terbilang($hasil_mod)));
        } else {
            return 'Data Salah';
        }
    }
}
