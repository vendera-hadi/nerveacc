<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

use App\Models\TrInvoice;
use App\Models\TrInvoiceDetail;
use App\Models\MsCompany;
use App\Models\MsConfig;
use PDF;

class InvoiceMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(TrInvoice $invoice)
    {
        $this->invoice = $invoice;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $inv_id = $this->invoice->id;
        if(!is_array($inv_id)) $inv_id = [$inv_id];

        $invoice_data = TrInvoice::select('tr_invoice.*', 'ms_unit.unit_code', 'ms_unit.va_utilities', 'ms_unit.va_maintenance')
                                ->join('tr_contract','tr_contract.id','=','tr_invoice.contr_id')
                                ->join('ms_unit','tr_contract.unit_id','=','ms_unit.id')
                                ->whereIn('tr_invoice.id',$inv_id)->with('MsTenant','InvoiceType')->get()->toArray();
        foreach ($invoice_data as $key => $inv) {
            $result = TrInvoiceDetail::select('tr_invoice_detail.id','tr_invoice_detail.invdt_amount','tr_invoice_detail.invdt_note','tr_period_meter.prdmet_id','tr_period_meter.prd_billing_date','tr_meter.meter_start','tr_meter.meter_end','tr_meter.meter_used','tr_meter.meter_cost','ms_cost_detail.costd_name')
            ->join('tr_invoice','tr_invoice.id',"=",'tr_invoice_detail.inv_id')
            ->leftJoin('ms_cost_detail','tr_invoice_detail.costd_id',"=",'ms_cost_detail.id')
            ->leftJoin('tr_meter','tr_meter.id',"=",'tr_invoice_detail.meter_id')
            ->leftJoin('tr_period_meter','tr_period_meter.id',"=",'tr_meter.prdmet_id')
            ->where('tr_invoice_detail.inv_id',$inv['id'])
            ->get()->toArray();
            $invoice_data[$key]['details'] = $result;
            $terbilang = $this->terbilang($inv['inv_amount']);
            $invoice_data[$key]['terbilang'] = '## '.$terbilang.' Rupiah ##';
        }
        $total = $invoice_data[0]['inv_outstanding'];

        $company = MsCompany::with('MsCashbank')->first()->toArray();
        $signature = @MsConfig::where('name','digital_signature')->first()->value;
        $signatureFlag = @MsConfig::where('name','invoice_signature_flag')->first()->value;

        $set_data = array(
            'invoice_data' => $invoice_data,
            'result' => $result,
            'company' => $company,
            'type' => null,
            'signature' => $signature,
            'signatureFlag' => $signatureFlag
        );
        $pdf = PDF::loadView('print_faktur', $set_data)->setPaper('a4')->output();
        return $this->view('print_faktur', $set_data)->subject('Tagihan '.$invoice_data[0]['inv_number'].' dari '.$company['comp_name'])->attachData($pdf, $invoice_data[0]['inv_number'].'.pdf', [
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