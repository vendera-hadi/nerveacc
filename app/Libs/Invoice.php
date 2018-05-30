<?php
namespace App\Libs;

use App\Models\TrInvoice;
use App\Models\TrInvoiceDetail;
use App\Models\TrContract;
use App\Models\MsInvoiceType;
use App\Models\MsConfig;
use App\Models\MsCompany;
use App\Models\MsCostItem;
use Auth, DB;

class Invoice {

    public function __construct()
    {
        $this->current_date = date('Y-m-d');
        $this->children = [];
        $this->grandTotal = 0;
        $this->taxPPN = 0;
        $this->companyData = MsCompany::first();
    }

    // GETTER
    public function getPeriodStart()
    {
        return $this->periodStart;
    }

    public function getPeriodEnd()
    {
        return $this->periodEnd;
    }

    public function getInvStartDate()
    {
        return $this->invStartDate;
    }

    // SETTER *WAJIB DISET*
    public function setPeriod($month, $year)
    {
        $this->month = $month;
        $this->month = str_pad($this->month, 2,'0',STR_PAD_LEFT);
        $this->year = $year;
        $this->periodStart = implode('-', [$this->year,$this->month,'01']);
        $this->periodEnd = date("Y-m-t", strtotime($this->periodStart));
        $this->setInvStartDate();
        $this->setInvEndDate();
    }

    public function setPeriodStart($date)
    {
        $this->periodStart = $date;
        $this->setInvStartDate();
    }

    public function setPeriodEnd($date)
    {
        $this->periodEnd = $date;
        $this->setInvEndDate();
    }

    public function setInvoiceType($invtypeId)
    {
        $this->invoiceType = MsInvoiceType::find($invtypeId);
    }

    public function setContract($contractId)
    {
        $this->contract = TrContract::find($contractId);
    }

    public function addChild($invdetail)
    {
        $detail = new TrInvoiceDetail;
        foreach ($invdetail as $key => $val) {
            $detail->$key = $val;
        }
        $this->children[] = $detail;
        // itungin amount
        $this->addAmount(@$invdetail['invdt_amount']);
    }

    // check denda keterlambatan dan add to inv detail
    public function addDenda()
    {
        $totalDenda = $this->totalDendaLastPeriod();
        $lastPeriode = date('F Y', strtotime($this->periodStart.' -1 month'));
        $coaDenda = @MsConfig::where('name','coa_denda')->first()->value;
        if($totalDenda > 0){
          $detail = [
                    'invdt_amount' => $totalDenda,
                    'invdt_note' => 'Late Payment Charges '.$lastPeriode,
                    'coa_code' => $coaDenda,
                    'costd_id' => 0
                ];
          $this->addChild($detail);
        }
    }

    // add PPN, posisi harus diletakkan belakangan
    public function addPPN()
    {
        if($this->usingPPN() && $this->grandTotal > 0){
            $coaPPN = @MsConfig::where('name','coa_ppn')->first()->value;
            $totalTax = round(0.1 * $this->grandTotal);
            $this->taxPPN = $totalTax;
            // $detail = [
            //       'invdt_amount' => $totalTax,
            //       'invdt_note' => 'PPN',
            //       'costd_id' => $coaPPN
            //   ];
            $detail = new TrInvoiceDetail;
            $detail->invdt_amount = $totalTax;
            $detail->invdt_note = 'PPN';
            $detail->coa_code = $coaPPN;
            $detail->costd_id = 0;
            // add ppn to inv detail
            $this->children[] = $detail;
            $this->grandTotal += $totalTax;
        }
    }

    // add materai
    public function addMaterai()
    {
        $stampData = MsCostItem::where('cost_code','STAMP')->first();
        $stampCoa = @$stampData->cost_coa_code;
        // total pay lebih dr 1jt
        if($this->grandTotal > $this->companyData->comp_materai2_amount){
            $invDetail[] = ['invdt_amount' => (float)$this->companyData->comp_materai2, 'invdt_note' => 'MATERAI', 'costd_id'=> 0, 'coa_code' => $stampCoa];
            $totalStamp = $this->companyData->comp_materai2;
        }else if($this->grandTotal >= $this->companyData->comp_materai1_amount && $this->grandTotal <= $this->companyData->comp_materai2_amount){
            // 250rb sampai 1jt
            $invDetail[] = ['invdt_amount' => (float)$this->companyData->comp_materai1, 'invdt_note' => 'MATERAI', 'costd_id'=> 0, 'coa_code' => $stampCoa];
            $totalStamp = $this->companyData->comp_materai1;
        }else{
            // under 250rb
            $totalStamp = 0;
        }

        if(!empty($totalStamp)){
            // $detail = [
            //       'invdt_amount' => (float)$totalStamp,
            //       'invdt_note' => 'MATERAI',
            //       'costd_id' => $stampCoa
            //   ];
            $detail = new TrInvoiceDetail;
            $detail->invdt_amount = (float)$totalStamp;
            $detail->invdt_note = 'MATERAI';
            $detail->coa_code = $stampCoa;
            $detail->costd_id = 0;
            // add ppn to inv detail
            $this->children[] = $detail;
            $this->grandTotal += $totalStamp;
        }
    }
    //  END SETTER

    public function create()
    {
        if(count($this->children) <= 0) return false;

        // save header
        $newInvoice = new TrInvoice;
        $newInvoice->tenan_id = $this->contract->tenan_id;
        $newInvoice->unit_id = $this->contract->unit_id;
        $newInvoice->inv_number = $this->newInvoiceNumber();
        $newInvoice->inv_faktur_no = $newInvoice->inv_number;
        $newInvoice->inv_faktur_date = $this->invStartDate;
        $newInvoice->inv_date = $this->invStartDate;
        $newInvoice->inv_duedate = $this->invEndDate;
        // TODO: hrs diitung
        $newInvoice->inv_amount = $this->grandTotal;
        $newInvoice->inv_ppn = $this->usingPPN() ? 0.1 : 0;
        $newInvoice->inv_outstanding = $this->grandTotal;
        $newInvoice->inv_ppn_amount = $this->taxPPN;
        $newInvoice->inv_post = 0;
        $newInvoice->invtp_id = $this->invoiceType->id;
        $newInvoice->contr_id = $this->contract->id;
        $newInvoice->created_by = Auth::id();
        $newInvoice->updated_by = $newInvoice->created_by;
        $newInvoice->footer = $this->getInvFooter();
        $newInvoice->label = $this->getInvLabel();
        $newInvoice->save();
        // return json_encode($this->children);
        // save details
        $newInvoice->TrInvoiceDetail()->saveMany($this->children);
        $this->children = [];
        return $newInvoice;
    }

    public function exists()
    {
        return TrInvoice::where('invtp_id',$this->invoiceType->id)->where('contr_id',$this->contract->id)->where('inv_date',$this->invStartDate)->where('inv_iscancel',0)->first();
    }

    public function getContract()
    {
        return $this->contract ? $this->contract : false;
    }

    /* PRIVATE FUNCTIONS */

    // get prefix invoice
    private function newPrefix()
    {
        $lastInvoiceofMonth = TrInvoice::select('inv_number')->where('inv_number','like', @$this->invoiceType->invtp_prefix.'-'.substr($this->year, -2).$this->month.'-%')->orderBy('id','desc')->first();
        try{
          if($lastInvoiceofMonth){
              $lastPrefix = explode('-', $lastInvoiceofMonth->inv_number);
              $lastPrefix = (int) $lastPrefix[2];
          }else{
              $lastPrefix = 0;
          }
        } catch(\Exception $e) {
            $lastPrefix = 0;
        }
        $newPrefix = $lastPrefix + 1;
        $newPrefix = str_pad($newPrefix, 4, 0, STR_PAD_LEFT);
        return $newPrefix;
    }

    // new invoice number
    private function newInvoiceNumber()
    {
        return @$this->invoiceType->invtp_prefix."-".substr($this->year, -2).$this->month."-".$this->newPrefix();
    }

    // set invoice start date
    private function setInvStartDate(){
        $config = @MsConfig::where('name','invoice_startdate')->first()->value;
        $variabelmonth = 0;
        if(@$this->invoiceType->id == 1) $variabelmonth = 1;
        try{
          if(!empty($config)){
              $config = str_pad($config, 2,'0',STR_PAD_LEFT);
              $startdate = date('Y-m-'.$config, strtotime($this->periodStart." +$variabelmonth month"));
          }else{
              $startdate = date('Y-m-01', strtotime($this->periodStart." +$variabelmonth month"));
          }
        } catch(\Exception $e) {
            $startdate = date('Y-m-01', strtotime($this->periodStart." +$variabelmonth month"));
        }
        $this->invStartDate = $startdate;

        if(date('Y-m-01',strtotime(@$this->contract->contr_startdate)) == $this->periodStart && @$this->invoiceType->id != 1){
            // jika contract maintenance / others dan masih di bulan yg sama dgn start contract date, tgl inv date jadiin tgl contract startdate
                $this->invStartDate = @$this->contract->contr_startdate;
        }
    }

    // set invoice end date
    private function setInvEndDate()
    {
        $config = @MsConfig::where('name','duedate_interval')->first()->value;
        try{
          $config = str_pad($config, 2,'0',STR_PAD_LEFT);
          if(empty($config))
            $duedate = date('Y-m-d', strtotime($this->invStartDate.' +15 days'));
          else
            $duedate = date('Y-m-'.$config, strtotime($this->periodStart.' +1 month'));
        } catch(\Exception $e) {
            $duedate = date('Y-m-d', strtotime($this->invStartDate.' +15 days'));
        }
        $this->invEndDate = $duedate;
    }

    // cek ppn using
    private function usingPPN()
    {
        return !empty(@$this->contract->MsTenant->tenan_isppn) ? true : false;
    }

    // add amount to grandtotal
    private function addAmount($amount)
    {
        if(!empty($amount)) $this->grandTotal += $amount;
    }

    // check denda last periode
    private function totalDendaLastPeriod()
    {
        // semua invoice periode lalu yg punya contract sama dan inv type id sama
        $invoices = TrInvoice::where('invtp_id',$this->invoiceType->id)
                  ->where('contr_id', $this->contract->id)
                  ->where(DB::raw('EXTRACT(MONTH FROM inv_date)'), date('n',strtotime($this->periodStart)) )
                  ->where(DB::raw('EXTRACT(YEAR FROM inv_date)'), date('Y',strtotime($this->periodStart)))
                  ->where('inv_post', 1)
                  ->get();

        // strtotime($this->periodStart." -1 month")

        $totalDenda = $hariTelat = 0;
        foreach ($invoices as $key => $inv) {
            // IF PPN sblm nya hanya ada PPN dan denda skip aja
            $count = 0;
            foreach ($inv->TrInvoiceDetail as $detail) {
                if($detail->invdt_note == "PPN" || strpos($detail->invdt_note, 'Late Payment Charges') !== false){
                    $count++;
                }
            }
            if($inv->TrInvoiceDetail->count() <= 2) return 0;

            // cek last payment of invoice
            $paydate = null;
            $paytotal = 0;
            foreach($inv->paymentdtl as $paymdtl){
                if(!$paymdtl->paymenthdr->status_void){
                    if(empty($paydate) || $paymdtl->paymenthdr->invpayh_date > $paydate){
                        $paydate = $paymdtl->paymenthdr->invpayh_date;
                    }
                    // cek pembayaran sblm tgl inv due date
                    if($paymdtl->paymenthdr->invpayh_date <= $inv->inv_duedate){
                        $paytotal += $paymdtl->invpayd_amount;
                    }
                }
            }
            // jika paydate ada, byr krg dr tgl JT dan outstanding saat itu = 0 artinya no denda
            if(!empty($paydate)){
                if($paydate <= $inv->inv_duedate){
                    // bayar sebelum jatuh tempo
                    if($inv->inv_outstanding <= 0){
                        // suda lunas, no denda
                    }else{
                        // belum lunas, denda
                        $totalDenda+= $this->countDenda($inv, $hariTelat);
                    }
                }else{
                    // bayar setelah jatuh tempo
                    if($inv->inv_outstanding <= 0){
                        // suda lunas, tp hitung denda sampai last pay
                        $hariTelat = $this->countTelat($inv, $paydate);
                    }else{
                        // belum lunas, denda
                        $hariTelat = $this->countTelat($inv);
                    }
                    $totalDenda+= $this->countDenda($inv, $hariTelat);
                }
            }else{
                // belum bayar, kena denda
                $hariTelat = $this->countTelat($inv);
                $totalDenda+= $this->countDenda($inv, $hariTelat);
            }
            // end denda
        }
        return $totalDenda;
    }

    // hitung brp jml hari telat
    private function countTelat($invoice, $dateLunas = null)
    {
        // tambahkan start denda
        $config = @MsConfig::where('name','start_denda')->first()->value;
        if(!empty(@$config)){
            $duedate = date_create(date('Y-m-d H:i:s', strtotime($invoice->inv_duedate." +$config days")));
        }else{
            $duedate = date_create($invoice->inv_duedate);
        }

        $nextPeriodStartDate = date('Y-m-d', strtotime($invoice->inv_date." +1 month"));
        // jika tgl pelunasan sudah lebih dari next periode inv date. hitung sampe akhir periode nya aj
        if(!empty($dateLunas) && $dateLunas > $nextPeriodStartDate) $dateLunas = null;
        $dendaDateEnd = !empty($dateLunas) ? $dateLunas : $nextPeriodStartDate;
        $dendaDateEnd = date_create($dendaDateEnd);

        $diff = date_diff($duedate, $dendaDateEnd);
        $hari = $diff->format("%a");
        return $hari;
    }

    // hitung jumlah denda keterlambatan
    private function countDenda($invoice, $days)
    {
        try{
            // get variabel denda
            $config = @MsConfig::where('name','denda_variable')->first()->value;
            // pengali denda * total invoice
            $denda = $config * $invoice->inv_amount;
            return round($denda);
        }catch(\Exception $e){
            return 0;
        }
    }

    private function getInvFooter()
    {
        return @MsConfig::where('name','footer_invoice')->first()->value;
    }

    private function getInvLabel()
    {
        return @MsConfig::where('name','footer_label_inv')->first()->value;
    }

}