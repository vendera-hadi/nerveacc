<?php
namespace App\Libs;

use App\Models\MsCostDetail;
use App\Models\TrInvoice;
use App\Models\TrContract;
use App\Models\TrContractInvoice;
use App\Models\TrPeriodMeter;
use App\Models\MsInvoiceType;
use App\Models\MsUnit;
use App\Models\MsConfig;
use App\Models\MsCompany;

class CostCreator {

    public function __construct()
    {
        $this->companyData = MsCompany::first();
    }

    // SETTER
    public function setCostItem($costitemId)
    {
        $this->costDetail = MsCostDetail::find($costitemId);
    }

    public function setInvType($invtypeId)
    {
        $this->invtp = MsInvoiceType::find($invtypeId);
    }

    public function setContract($contractId)
    {
        $this->contract = TrContract::find($contractId);
    }

    public function setInvStartDate($date)
    {
        $this->invStartDate = $date;
    }

    public function setPeriod($periodStart, $periodEnd)
    {
        $this->periodStart = $periodStart;
        $this->periodEnd = $periodEnd;
    }

    private function setPeriodMeter()
    {
        $this->lastPeriodMeter = $this->getLastPeriodMeter();
        if($this->lastPeriodMeter){
            $this->meter = $this->lastPeriodMeter->meter->where('costd_id',$this->costDetail->id)->where('contr_id',$this->contract->id)->first();
            if($this->meter){
                $this->detailAmount = (float) round($this->meter->total);
                return true;
            }else{
                // meter dont exist
                echo "<br>Contract Contract Code <strong>".$this->contract->contr_code."</strong> Cost Item <strong>".$this->costDetail->costd_name."</strong>, Meter ID is not inputed yet<br>";
            }
        }else{
            // error belum menginput last period meter
            echo "<br>Contract  Meter Input for ".date('F Y',strtotime($this->periodStart)).' was not inputed yet. Go to <a href="'.url('period_meter').'">Meter Input</a> and create Period then Input Meter of this particular month<br>';
        }
        return false;
    }

    // GETTER
    public function generateDetail()
    {
        if($this->validate()){
            if($this->isMeter()){
                return $this->generateMeter();
            }else{
                return $this->generateNonMeter();
            }
        }
    }

    public function generateMeter()
    {
        if($this->setPeriodMeter()){
            // generate
            if($this->costDetail->cost_id == 1){
                return $this->generateElectricity();
            }else if($this->costDetail->cost_id == 2){
                return $this->generateWater();
            }else{
                return $this->generateOtherMeter();
            }
        }
        return false;
    }

    public function generateNonMeter()
    {
        if($this->validateNonMeter()){
            if($this->costDetail->costitem->is_service_charge){
                return $this->generateServiceCharge();
            }else if($this->costDetail->costitem->is_sinking_fund){
                return $this->generateSinkingFund();
            }else if($this->costDetail->costitem->is_insurance){
                return $this->generateInsurance();
            }else{
                return $this->generateOtherNonMeter();
            }
        }
        return false;
    }

    private function generateServiceCharge()
    {
        if(!$this->checkLastInvoicePeriodRumus()) return false;
        $currUnit = MsUnit::find($this->contract->unit_id);
        $alias = @MsConfig::where('name','service_charge_alias')->first()->value;

        if($this->checkNeedProRate()){
            $kelebihanHari = $this->getProRateDay();
            $totalDayOfMonth = date('t',strtotime($this->contract->contr_startdate));
            $amountPerMonth = $currUnit->unit_sqrt * $this->costDetail->costd_rate;
            $proRateAmount = $kelebihanHari / $totalDayOfMonth * $amountPerMonth;

            $note = $alias." ".date('F Y',strtotime($this->periodStart))." (ProRate $kelebihanHari / $totalDayOfMonth days) ";
            if($this->monthGapNext > 0) $note .= "s/d ".date('F Y',strtotime($this->periodStart." +".$this->monthGapNext." months"));
            $note .= "<br>".number_format($currUnit->unit_sqrt,2)."M2 x Rp. ".number_format($this->costDetail->costd_rate)." x (".$this->monthGapNext." months +  $kelebihanHari days)";
            $amount = ($currUnit->unit_sqrt * $this->costDetail->costd_rate * $this->monthGapNext) + $proRateAmount + $this->costDetail->costd_burden + $this->costDetail->costd_admin;
        }else{
            $note = $alias." ".date('F Y',strtotime($this->periodStart));
            if($this->monthGapNext > 0) $note .= " s/d ".date('F Y',strtotime($this->periodStart." +".$this->monthGapNext." months"));
            $note .= "<br>".number_format($currUnit->unit_sqrt,2)."M2 x Rp. ".number_format($this->costDetail->costd_rate)." x ".($this->monthGapNext + 1)." months";
            $amount = ($currUnit->unit_sqrt * $this->costDetail->costd_rate * ($this->monthGapNext + 1)) + $this->costDetail->costd_burden + $this->costDetail->costd_admin;
        }
        $this->detailAmount = round($amount);
        return $this->defineOutput($note);
    }

    private function generateSinkingFund()
    {
        if(!$this->checkLastInvoicePeriodRumus()) return false;
        $currUnit = MsUnit::find($this->contract->unit_id);

        if($this->checkNeedProRate()){
            $kelebihanHari = $this->getProRateDay();
            $totalDayOfMonth = date('t',strtotime($this->contract->contr_startdate));
            $amountPerMonth = $currUnit->unit_sqrt * $this->costDetail->costd_rate;
            $proRateAmount = $kelebihanHari / $totalDayOfMonth * $amountPerMonth;

            $note = $this->costDetail->costd_name." (SF)  ".date('F Y',strtotime($this->periodStart))." (ProRate $kelebihanHari / $totalDayOfMonth days)" ;
            if($this->monthGapNext > 0) $note .= " s/d ".date('F Y',strtotime($this->periodStart." +".$this->monthGapNext." months"));
            $note .= "<br>".number_format($currUnit->unit_sqrt,2)."M2 x Rp. ".number_format($this->costDetail->costd_rate)." x (".$this->monthGapNext." months + $kelebihanHari days)";
            $amount = ($currUnit->unit_sqrt * $this->costDetail->costd_rate * $this->monthGapNext) + $proRateAmount + $this->costDetail->costd_burden + $this->costDetail->costd_admin;
        }else{
            $note = $this->costDetail->costd_name." (SF)  ".date('F Y',strtotime($this->periodStart));

            if($this->monthGapNext > 0) $note .= " s/d ".date('F Y',strtotime($this->periodStart." +".$this->monthGapNext." months"));
            $note .= "<br>".number_format($currUnit->unit_sqrt,2)."M2 x Rp. ".number_format($this->costDetail->costd_rate)." x ".($this->monthGapNext + 1)." months";
            $amount = ($currUnit->unit_sqrt * $this->costDetail->costd_rate * ($this->monthGapNext + 1)) + $this->costDetail->costd_burden + $this->costDetail->costd_admin;
        }
        $this->detailAmount = round($amount);
        return $this->defineOutput($note);
    }

    private function generateInsurance()
    {
        if(!$this->checkLastInvoicePeriod()) return false;
        $currUnit = MsUnit::find($this->contract->unit_id);
        $ctrInv = $this->getContractInvoice();
        $npp_building = $this->companyData->comp_npp_insurance;
        // npp unit  = lust unit per luas total unit
        $npp_unit =  $currUnit->unit_sqrt / $this->companyData->comp_sqrt;
        $note = $this->costDetail->costd_name." (Rp. ".number_format($this->costDetail->costd_rate,2)."/".number_format($npp_building,2)." x ".$npp_unit.") Periode ".date('F Y',strtotime($this->periodStart))." s/d ".date('F Y',strtotime($this->periodStart." +".$ctrInv->continv_period." months"));
        // rumus cost + burden + admin
        $amount = $this->costDetail->costd_rate / $npp_building * $npp_unit;
        $this->detailAmount = round($amount,2);
        return $this->defineOutput($note);
    }

    private function generateOtherNonMeter()
    {
        if(!$this->checkLastInvoicePeriodRumus()) return false;

        // sementara blum tambahkan pro rate
        $note = $this->costDetail->costd_name." Periode ".date('F Y',strtotime($this->periodStart));
        if($this->monthGapNext > 1) $note .= " s/d ".date('F Y',strtotime($this->periodStart." +".$this->monthGapNext." months"));
        // rumus cost + burden + admin
        $amount = $this->costDetail->costd_rate + $this->costDetail->costd_burden + $this->costDetail->costd_admin;
        $this->detailAmount = round($amount,2);
        return $this->defineOutput($note);
    }

    private function validateNonMeter()
    {
        $result = false;
        if(!empty($this->contract->contr_terminate_date) && ($this->periodEnd > $this->contract->contr_terminate_date)){
            // JIKA CONTRACT TERMINATE DATE BERAKHIR BULAN INI
            echo "<br>Contract  terminated at ".date('d/m/Y',strtotime($this->contract->contr_terminate_date)).", Please CLOSE this Contract <a href=\"".route('contract.unclosed')."\">Here</a>";
        }else if($this->periodEnd > $this->contract->contr_enddate){
            // JIKA CONTRACT SUDAH BERAKHIR
            echo "<br>Contract  expired at ".date('d/m/Y',strtotime($this->contract->contr_enddate)).", Please CLOSE this Contract <a href=\"".route('contract.unclosed')."\">Here</a>";
        }else{
            $result = true;
        }
        return $result;
    }

    private function generateElectricity()
    {
        // electricity
        $note = $this->costDetail->costd_name." : ".date('d/m/Y',strtotime($this->lastPeriodMeter->prdmet_start_date))." - ".date('d/m/Y',strtotime($this->lastPeriodMeter->prdmet_end_date))."<br>Meter Awal : ".number_format($this->meter->meter_start,0)."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Meter Akhir : ".number_format($this->meter->meter_end,0)."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Pemakaian : ".number_format($this->meter->meter_used,2);
        return $this->defineOutput($note);
    }

    private function generateWater()
    {
        // water
        $note = $this->costDetail->costd_name." : ".date('d/m/Y',strtotime($this->lastPeriodMeter->prdmet_start_date))." - ".date('d/m/Y',strtotime($this->lastPeriodMeter->prdmet_end_date))."<br>Meter Awal : ".number_format($this->meter->meter_start,0)."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Meter Akhir : ".number_format($this->meter->meter_end,0)."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Pemakaian : ".number_format($this->meter->meter_used,0)."<br>Biaya Pemakaian : ".number_format($this->meter->meter_used,0);
        return $this->defineOutput($note);
    }

    private function generateOtherMeter()
    {
        $note = $this->costDetail->costd_name."<br>Konsumsi : ".number_format($this->meter->meter_used,0)." ".$this->costDetail->costd_unit." Per ".date('d/m/Y',strtotime($this->lastPeriodMeter->prdmet_start_date))." - ".date('d/m/Y',strtotime($this->lastPeriodMeter->prdmet_end_date));
        return $this->defineOutput($note);
    }

    private function defineOutput($note)
    {
        return [
                'invdt_amount' => $this->detailAmount,
                'invdt_note' => $note,
                'costd_id' => $this->costDetail->id,
                'meter_id' => !empty(@$this->meter->id) ? $this->meter->id : 0
            ];
    }

    private function validate()
    {
        $result = true;
        // check last invoice dan expired kapan
        if(!$this->checkLastInvoicePeriod() && $this->isMeter()){
            $result = false;
        }
        return $result;
    }

    private function checkLastInvoicePeriod()
    {
        $result = false;
        $invoice = TrInvoice::where('invtp_id', $this->invtp->id)->where('contr_id', $this->contract->id)->where('inv_date','<',$this->invStartDate)->orderBy('id', 'desc')->first();
        $ctrInv = $this->getContractInvoice();
        if(!empty($invoice)){
            // cek inv date lalu + period apa sudah lewat ?
            $estimatedEndInv = date('Y-m-d', strtotime($invoice->inv_date." +".$ctrInv->continv_period." months"));
            if($this->invStartDate >= $estimatedEndInv){
                // suda lewat
                $result = true;
            }
        }else{
            $result = true;
        }
        return $result;
    }

    private function checkLastInvoicePeriodRumus()
    {
        $result = false;
        $this->monthGapNext = $this->monthGapPrev = 0;

        // RUMUS KHUSUS PERIODE
        $ctrInv = $this->getContractInvoice();
        $period = $ctrInv->continv_period;
        $period_classifications = [];
        for($i=1; $i<=12; $i++){
            $temp[] = $i;
            if($i%$period == 0){
                $period_classifications[] = $temp;
                $temp = [];
            }
        }
        $currentmonth = date('m',strtotime($this->periodStart));
        $currentmonth = (int)$currentmonth;
        foreach ($period_classifications as $pval) {
            if(in_array($currentmonth, $pval)){
                $first = reset($pval);
                $last = end($pval);
                $this->monthGapPrev = $currentmonth - $first;
                $this->monthGapNext = $last - $currentmonth;
            }
        }
        // echo "<br>MONTH GAP : ".$this->monthGapNext."<br>";
        if(date('m',strtotime($this->periodStart)) % $period == 1){
            // suda lewat
            $result = true;
        }
        return $result;
    }

    private function getLastPeriodMeter()
    {
        $month = date('m', strtotime($this->periodStart));
        return TrPeriodMeter::where(\DB::raw('EXTRACT(month from prd_billing_date)'),'=', $month)->where('status',1)->orderBy('id','desc')->first();
    }

    private function getContractInvoice()
    {
        return TrContractInvoice::where('invtp_id',$this->invtp->id)->where('contr_id',$this->contract->id)->where('costd_id', $this->costDetail->id)->first();
    }

    private function isMeter()
    {
        return !empty($this->costDetail->costd_ismeter) ? true : false;
    }

    private function checkNeedProRate()
    {
        $firstDayofContractStartDate = date('Y-m-01',strtotime($this->contract->contr_startdate));
        if($firstDayofContractStartDate == $this->periodStart){
            // if memiliki bulan yg sama
            if(date('d',strtotime($this->contract->contr_startdate)) != 1){
                return true;
            }
        }
        return false;
    }

    private function getProRateDay()
    {
        $dayOfStartContract = date('d',strtotime($this->contract->contr_startdate));
        $endOfMonth = date('t',strtotime($this->contract->contr_startdate));
        $selisih = $endOfMonth - $dayOfStartContract;
        return !empty($selisih) ? $selisih : 1;
    }
}