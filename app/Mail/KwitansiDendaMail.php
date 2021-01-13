<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

use App\Models\TrLedger;
use App\Models\TrInvoice;
use App\Models\TrBankJv;
use App\Models\MsCashBank;
use App\Models\MsPaymentType;
use App\Models\MsTenant;
use App\Models\MsUnit;
use App\Models\MsUnitOwner;
use App\Models\TrInvoicePaymhdr;
use App\Models\TrInvoicePaymdtl;
use App\Models\TrInvpaymJournal;
use App\Models\MsMasterCoa;
use App\Models\MsJournalType;
use App\Models\MsConfig;
use App\Models\TrBank;
use App\Models\EmailQueue;
use App\Models\KwitansiCounter;
use App\Models\Numcounter;
use App\Models\TrDendaPayment;
use App\Models\ReminderH;
use App\Models\MsCompany;
use PDF;

class KwitansiDendaMail extends Mailable
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
        $this->paymentHeader = TrDendaPayment::find($paymentHeader);
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
        $paymentHeader = TrDendaPayment::join('ms_unit','ms_unit.id','=','tr_denda_payment.unit_id')->join('ms_tenant','ms_tenant.id','=','tr_denda_payment.tenan_id')->find($this->paymentHeader->id);
        $terbilang = $this->terbilang($paymentHeader->denda_amount);
        $set_data = array(
                'company' => $company,
                'header' => $paymentHeader,
                'terbilang' => $terbilang.' Rupiah',
                'tenan' => $paymentHeader->tenan_name,
                'unit' => $paymentHeader->unit_code,
                'type' =>'mail'
            );
        $view = 'print_denda';
        $pdf = PDF::loadView($view, $set_data)->setPaper('a4')->output();
        return $this->view($view, $set_data)->subject("Kwitansi Denda")->attachData($pdf, "Kwitansi Denda.pdf", [
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
