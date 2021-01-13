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
use App\Libs\ElectricityCalc;
use App\Libs\WaterCalc;
use App\Libs\GeneralCalc;

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
                $this->detailAmount = (float) round($this->meter->total,2);
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

    public function setMeter($meter)
    {
        $this->meter = $meter;
    }

    public function setCustomAmount($amount)
    {
        $this->detailAmount = $amount;
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

    public function generateCutoffNonMeter()
    {
        if($this->validate()){
            if($this->costDetail->costitem->is_service_charge){
                return $this->generateCutoffServiceCharge();
            }else if($this->costDetail->costitem->is_sinking_fund){
                return $this->generateCutoffSinkingFund();
            }else if($this->costDetail->costitem->is_insurance){
                return $this->generateInsurance();
            }else{
                return $this->generateOtherNonMeter();
            }
        }
        return false;
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
            }else if($this->costDetail->costitem->is_sewa){
                return $this->generateSewa();
            }else{
                return $this->generateOtherNonMeter();
            }
        }
        return false;
    }

    public function getCostItem()
    {
        return $this->costDetail;
    }

    private function generateServiceCharge()
    {
        if(!empty(@$this->costDetail->year_cycle)){
            if(!$this->checkLastInvoicePeriodRumus()) return false;
        }else{
            $ctrInv = $this->getContractInvoice();
            $this->monthGapNext = $ctrInv->continv_period - 1;
        }
        $currUnit = MsUnit::find($this->contract->unit_id);
        $alias = @MsConfig::where('name','service_charge_alias')->first()->value;
        $pro_val = 0;
        if($this->checkNeedProRate()){
            $kelebihanHari = $this->getProRateDay();
            $totalDayOfMonth = date('t',strtotime($this->contract->contr_startdate));
            $amountPerMonth = CEIL($currUnit->unit_sqrt * $this->costDetail->costd_rate);
            $proRateAmount = CEIL($kelebihanHari / $totalDayOfMonth * $amountPerMonth);

            $note = $alias." ".date('F Y',strtotime($this->periodStart))." (ProRate $kelebihanHari / $totalDayOfMonth days) ";
            if($this->monthGapNext > 0) $note .= "s/d ".date('F Y',strtotime($this->periodStart." +".$this->monthGapNext." months"));
            $note .= "<br>".number_format($currUnit->unit_sqrt,2)."M2 x Rp. ".number_format($this->costDetail->costd_rate)." x (".$this->monthGapNext." months +  $kelebihanHari days)";
            $amount = CEIL(($currUnit->unit_sqrt * $this->costDetail->costd_rate * $this->monthGapNext) + $proRateAmount + $this->costDetail->costd_burden);
            if($this->costDetail->costd_admin_type == 'percent'){
                $amount += CEIL($this->costDetail->costd_admin/100 * $amount);
            }else{
                $amount += CEIL($this->costDetail->costd_admin);
            }
            $pro_val = $proRateAmount;
        }else{
            $note = $alias." ".date('F Y',strtotime($this->periodStart));
            if($this->monthGapNext > 0) $note .= " s/d ".date('F Y',strtotime($this->periodStart." +".$this->monthGapNext." months"));
            $note .= "<br>".number_format($currUnit->unit_sqrt,2)."M2 x Rp. ".number_format($this->costDetail->costd_rate)." x ".($this->monthGapNext + 1)." months";
            $amount = ($currUnit->unit_sqrt * $this->costDetail->costd_rate * ($this->monthGapNext + 1)) + $this->costDetail->costd_burden;
            if($this->costDetail->costd_admin_type == 'percent'){
                $amount += CEIL($this->costDetail->costd_admin/100 * $amount);
            }else{
                $amount += CEIL($this->costDetail->costd_admin);
            }
        }
        $this->detailAmount = round($amount);
        return $this->defineOutput($note,$pro_val);
    }

    private function generateCutoffServiceCharge()
    {
        $currUnit = MsUnit::find($this->contract->unit_id);
        $alias = @MsConfig::where('name','service_charge_alias')->first()->value;

        $start = new \DateTime($this->periodStart);
        $end = new \DateTime($this->periodEnd);
        $diff = $start->diff($end);

        $kelebihanHari = $diff->format('%d');
        $totalDayOfMonth = date('t',strtotime($this->periodEnd));
        $amountPerMonth = CEIL($currUnit->unit_sqrt * $this->costDetail->costd_rate);
        $proRateAmount = CEIL($kelebihanHari / $totalDayOfMonth * $amountPerMonth);

        $note = $alias." ".date('d F Y',strtotime($this->periodStart))." s/d ".date('d F Y',strtotime($this->periodEnd));
        $note .= "<br>".number_format($currUnit->unit_sqrt,2)."M2 x Rp. ".number_format($this->costDetail->costd_rate)." x (".(!empty($diff->format('%m')) ? $diff->format('%m')."months" : "" )." ".$diff->format('%d')." / $totalDayOfMonth days)";

        $amount = ($currUnit->unit_sqrt * $this->costDetail->costd_rate * $diff->format('%m')) + $proRateAmount + $this->costDetail->costd_burden;
        if($this->costDetail->costd_admin_type == 'percent'){
            $amount += CEIL($this->costDetail->costd_admin/100 * $amount);
        }else{
            $amount += CEIL($this->costDetail->costd_admin);
        }
        $this->detailAmount = round($amount);
        return $this->defineOutput($note);
    }

    private function generateSinkingFund()
    {
        if(!empty(@$this->costDetail->year_cycle)){
            if(!$this->checkLastInvoicePeriodRumus()) return false;
        }else{
            $ctrInv = $this->getContractInvoice();
            $this->monthGapNext = $ctrInv->continv_period - 1;
        }
        $currUnit = MsUnit::find($this->contract->unit_id);
        $pro_val = 0;
        if($this->checkNeedProRate()){
            $kelebihanHari = $this->getProRateDay();
            $totalDayOfMonth = date('t',strtotime($this->contract->contr_startdate));
            $amountPerMonth = CEIL($currUnit->unit_sqrt * $this->costDetail->costd_rate);
            $proRateAmount = CEIL($kelebihanHari / $totalDayOfMonth * $amountPerMonth);

            $note = $this->costDetail->costd_name."  ".date('F Y',strtotime($this->periodStart))." (ProRate $kelebihanHari / $totalDayOfMonth days)" ;
            if($this->monthGapNext > 0) $note .= " s/d ".date('F Y',strtotime($this->periodStart." +".$this->monthGapNext." months"));
            $note .= "<br>".number_format($currUnit->unit_sqrt,2)."M2 x Rp. ".number_format($this->costDetail->costd_rate)." x (".$this->monthGapNext." months + $kelebihanHari days)";
            $amount = CEIL(($currUnit->unit_sqrt * $this->costDetail->costd_rate * $this->monthGapNext) + $proRateAmount + $this->costDetail->costd_burden);
            if($this->costDetail->costd_admin_type == 'percent'){
                $amount += CEIL($this->costDetail->costd_admin/100 * $amount);
            }else{
                $amount += CEIL($this->costDetail->costd_admin);
            }
            $pro_val = $proRateAmount;
        }else{
            $note = $this->costDetail->costd_name."  ".date('F Y',strtotime($this->periodStart));

            if($this->monthGapNext > 0) $note .= " s/d ".date('F Y',strtotime($this->periodStart." +".$this->monthGapNext." months"));
            $note .= "<br>".number_format($currUnit->unit_sqrt,2)."M2 x Rp. ".number_format($this->costDetail->costd_rate)." x ".($this->monthGapNext + 1)." months";
            $amount = ($currUnit->unit_sqrt * $this->costDetail->costd_rate * ($this->monthGapNext + 1)) + $this->costDetail->costd_burden;
            if($this->costDetail->costd_admin_type == 'percent'){
                $amount += CEIL($this->costDetail->costd_admin/100 * $amount);
            }else{
                $amount += CEIL($this->costDetail->costd_admin);
            }
        }
        $this->detailAmount = round($amount);
        return $this->defineOutput($note,$pro_val);
    }

    private function generateCutoffSinkingFund()
    {
        $currUnit = MsUnit::find($this->contract->unit_id);

        $start = new \DateTime($this->periodStart);
        $end = new \DateTime($this->periodEnd);
        $diff = $start->diff($end);

        $kelebihanHari = $diff->format('%d');
        $totalDayOfMonth = date('t',strtotime($this->periodEnd));
        $amountPerMonth = CEIL($currUnit->unit_sqrt * $this->costDetail->costd_rate);
        $proRateAmount = CEIL($kelebihanHari / $totalDayOfMonth * $amountPerMonth);

        $note = $this->costDetail->costd_name."  ".date('d F Y',strtotime($this->periodStart))." s/d ".date('d F Y',strtotime($this->periodEnd));
        $note .= "<br>".number_format($currUnit->unit_sqrt,2)."M2 x Rp. ".number_format($this->costDetail->costd_rate)." x (".(!empty($diff->format('%m')) ? $diff->format('%m')."months" : "" )." $kelebihanHari / $totalDayOfMonth days)";

        $amount = ($currUnit->unit_sqrt * $this->costDetail->costd_rate * $diff->format('%m')) + $proRateAmount + $this->costDetail->costd_burden;
        if($this->costDetail->costd_admin_type == 'percent'){
            $amount += CEIL($this->costDetail->costd_admin/100 * $amount);
        }else{
            $amount += CEIL($this->costDetail->costd_admin);
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
        $npp_unit =  CEIL($currUnit->unit_sqrt / $this->companyData->comp_sqrt);
        $note = $this->costDetail->costd_name." (Rp. ".number_format($this->costDetail->costd_rate,2)."/".number_format($npp_building,2)." x ".$npp_unit.") Periode ".date('F Y',strtotime($this->periodStart))." s/d ".date('F Y',strtotime($this->periodStart." +".$ctrInv->continv_period." months"));
        // rumus cost + burden + admin
<<<<<<< Updated upstream
        $amount = $this->costDetail->costd_rate / $npp_building * $npp_unit;
=======
        $amount = CEIL($this->costDetail->costd_rate / $npp_building * $npp_unit);
        if($this->costDetail->costd_admin_type == 'percent'){
            $amount += CEIL($this->costDetail->costd_admin/100 * $amount);
        }else{
            $amount += CEIL($this->costDetail->costd_admin);
        }
>>>>>>> Stashed changes
        $this->detailAmount = round($amount);
        return $this->defineOutput($note);
    }

    private function generateOtherNonMeter()
    {
        if(!$this->checkLastInvoicePeriodRumus()) return false;

        // sementara blum tambahkan pro rate
        $note = $this->costDetail->costd_name." Periode ".date('F Y',strtotime($this->periodStart));
        if($this->monthGapNext > 1) $note .= " s/d ".date('F Y',strtotime($this->periodStart." +".$this->monthGapNext." months"));
        // rumus cost + burden + admin
        $amount = CEIL($this->costDetail->costd_rate + $this->costDetail->costd_burden);
        if($this->costDetail->costd_admin_type == 'percent'){
            $amount += CEIL($this->costDetail->costd_admin/100 * $amount);
        }else{
            $amount += CEIL($this->costDetail->costd_admin);
        }
        $this->detailAmount = round($amount);
        return $this->defineOutput($note);
    }

    private function validateNonMeter()
    {
        $result = false;
        if(!empty($this->contract->contr_terminate_date) && ($this->periodEnd > $this->contract->contr_terminate_date)){
            // JIKA CONTRACT TERMINATE DATE BERAKHIR BULAN INI
            echo "<br>Contract  terminated at ".date('d/m/Y',strtotime($this->contract->contr_terminate_date)).", Please CLOSE this Contract <a href=\"".route('contract.unclosed')."\">Here</a><br>";
        }else if($this->periodEnd > $this->contract->contr_enddate){
            // JIKA CONTRACT SUDAH BERAKHIR
            echo "<br>Contract  expired at ".date('d/m/Y',strtotime($this->contract->contr_enddate)).", Please CLOSE this Contract <a href=\"".route('contract.unclosed')."\">Here</a><br>";
        }else{
            $result = true;
        }
        return $result;
    }

    private function generateElectricity()
    {
        // electricity
        $bpju = @MsConfig::where('name','ppju')->first()->value;
        $public_area = @$this->costDetail->percentage;
        if(@$this->costDetail->value_type == 'percent') $public_area = $public_area." %";
        $note = $this->costDetail->costd_name." : ".date('d/m/Y',strtotime($this->lastPeriodMeter->prdmet_start_date))." - ".date('d/m/Y',strtotime($this->lastPeriodMeter->prdmet_end_date));
        if(!empty(@$this->costDetail->costd_show_detail))
            $note .= "<br>Awal : ".number_format($this->meter->meter_start,2)."&nbsp;&nbsp;&nbsp; Akhir : ".number_format($this->meter->meter_end,2)."&nbsp;&nbsp;&nbsp; Pakai : ".number_format($this->meter->meter_used,2)."&nbsp;&nbsp;&nbsp;Abodemen : ".number_format($this->meter->meter_burden,2)."&nbsp;&nbsp;&nbsp;Tarif (/kWh): ".number_format($this->costDetail->costd_rate,2)."&nbsp;&nbsp;&nbsp;PPJU : ".$bpju."% &nbsp;&nbsp;&nbsp;Beban Bersama : ".$public_area;
        return $this->defineOutput($note);
    }

    private function generateWater()
    {
        // water
        $note = $this->costDetail->costd_name." : ".date('d/m/Y',strtotime($this->lastPeriodMeter->prdmet_start_date))." - ".date('d/m/Y',strtotime($this->lastPeriodMeter->prdmet_end_date));
        if(!empty(@$this->costDetail->costd_show_detail))
            $note .= "<br>Awal : ".number_format($this->meter->meter_start,2)."&nbsp;&nbsp;&nbsp; Akhir : ".number_format($this->meter->meter_end,2)."&nbsp;&nbsp;&nbsp; Pakai : ".number_format($this->meter->meter_used,2)."&nbsp;&nbsp;&nbsp; Tarif (per M3) : ".number_format($this->costDetail->costd_rate,2)."&nbsp;&nbsp;&nbsp;Abodemen : ".number_format($this->meter->meter_burden,2)."&nbsp;&nbsp;&nbsp;Adm : ".number_format($this->meter->meter_admin,2);
        return $this->defineOutput($note);
    }

    // additional JUN18
    private function generateSewa()
    {
        if(!$this->checkLastInvoicePeriod()) return false;
        $ctrInv = $this->getContractInvoice();
        $period = $ctrInv->continv_period;
        // periode berjalan tidak perlu rumus check periode
        $dateContract = date('d',strtotime($this->contract->contr_startdate));
        $startInvoice = date('Y-m-'.$dateContract, strtotime($this->periodStart));
        $endInvoice = date('Y-m-d', strtotime($startInvoice." +".$period." months - 1 day"));
        $note = $this->costDetail->costd_name." Periode ".date('d F Y',strtotime($startInvoice))." s/d ".date('d F Y',strtotime($endInvoice));

        // rumus luas area * cost rate * lama per bln
        $currUnit = MsUnit::find($this->contract->unit_id);
        $amount = CEIL($this->costDetail->costd_rate * $period);
        $this->detailAmount = round($amount);
        return $this->defineOutput($note);
    }

    private function generateOtherMeter()
    {
        $note = $this->costDetail->costd_name."<br>Konsumsi : ".number_format($this->meter->meter_used,2)." ".$this->costDetail->costd_unit." Per ".date('d/m/Y',strtotime($this->lastPeriodMeter->prdmet_start_date))." - ".date('d/m/Y',strtotime($this->lastPeriodMeter->prdmet_end_date));
        return $this->defineOutput($note);
    }

    public function defineOutput($note,$prorate=0)
    {
        return [
                'invdt_amount' => $this->detailAmount,
                'prorate'=>$prorate,
                'invdt_note' => $note,
                'costd_id' => $this->costDetail->id,
                'meter_id' => !empty(@$this->meter->id) ? $this->meter->id : 0
            ];
    }

    private function validate()
    {
        $result = true;
        // check last invoice dan expired kapan
        if(!$this->checkLastInvoicePeriod() && !$this->isMeter()){
            $result = false;
        }
        return $result;
    }

    private function checkLastInvoicePeriod()
    {
        $result = false;
        $invoice = TrInvoice::where('invtp_id', $this->invtp->id)->where('unit_id',$this->contract->unit_id)->where('inv_date','<',$this->invStartDate)->where('inv_iscancel',0)->orderBy('id', 'desc')->first();
        $ctrInv = $this->getContractInvoice();
        if(!empty($invoice)){
            // cek inv date lalu + period apa sudah lewat ?
            //CEK JENIS INVOICE
            if($this->invtp->id == 2){
                $periode = $ctrInv->continv_period;
                $tgl = date('d', strtotime($invoice->inv_date));
                $tgl2 = date('d', strtotime($this->periodStart));
                $bln = date('n', strtotime($invoice->inv_date));
                $estimatedEndInv = date('Y-m-01', strtotime($invoice->inv_date." +".$ctrInv->continv_period." months"));
                switch ($periode) {
                    case '3':
                        if($tgl == '5'){
                            $estimatedEndInv = date('Y-m-01', strtotime($invoice->inv_date." +".$ctrInv->continv_period." months"));
                        }else if($tgl != $tgl2){
                            switch ($bln) {
                                case '12':
                                    $estimatedEndInv = date('Y-12-05', strtotime($invoice->inv_date));
                                    break;
                                case '11':
                                    $estimatedEndInv = date('Y-12-05', strtotime($invoice->inv_date));
                                    break;
                                case '10':
                                    $estimatedEndInv = date('Y-12-05', strtotime($invoice->inv_date));
                                    break;
                                case '9':
                                    $estimatedEndInv = date('Y-10-05', strtotime($invoice->inv_date));
                                    break;
                                case '8':
                                    $estimatedEndInv = date('Y-10-05', strtotime($invoice->inv_date));
                                    break;
                                case '7':
                                    $estimatedEndInv = date('Y-10-05', strtotime($invoice->inv_date));
                                    break;
                                case '6':
                                    $estimatedEndInv = date('Y-07-05', strtotime($invoice->inv_date));
                                    break;
                                case '5':
                                    $estimatedEndInv = date('Y-07-05', strtotime($invoice->inv_date));
                                    break;
                                case '4':
                                    $estimatedEndInv = date('Y-07-05', strtotime($invoice->inv_date));
                                    break;
                                case '3':
                                    $estimatedEndInv = date('Y-03-05', strtotime($invoice->inv_date));
                                    break;
                                case '2':
                                    $estimatedEndInv = date('Y-03-05', strtotime($invoice->inv_date));
                                    break;
                                case '1':
                                    $estimatedEndInv = date('Y-03-05', strtotime($invoice->inv_date));
                                    break;
                                default:
                                    # code...
                                    break;
                            }
                        }else{
                            switch ($bln) {
                                case '12':
                                    $estimatedEndInv = date('Y-12-05', strtotime($invoice->inv_date));
                                    break;
                                case '11':
                                    $estimatedEndInv = date('Y-12-05', strtotime($invoice->inv_date));
                                    break;
                                case '10':
                                    $estimatedEndInv = date('Y-12-05', strtotime($invoice->inv_date));
                                    break;
                                case '9':
                                    $estimatedEndInv = date('Y-10-05', strtotime($invoice->inv_date));
                                    break;
                                case '8':
                                    $estimatedEndInv = date('Y-10-05', strtotime($invoice->inv_date));
                                    break;
                                case '7':
                                    $estimatedEndInv = date('Y-10-05', strtotime($invoice->inv_date));
                                    break;
                                case '6':
                                    $estimatedEndInv = date('Y-07-05', strtotime($invoice->inv_date));
                                    break;
                                case '5':
                                    $estimatedEndInv = date('Y-07-05', strtotime($invoice->inv_date));
                                    break;
                                case '4':
                                    $estimatedEndInv = date('Y-07-05', strtotime($invoice->inv_date));
                                    break;
                                case '3':
                                    $estimatedEndInv = date('Y-03-05', strtotime($invoice->inv_date));
                                    break;
                                case '2':
                                    $estimatedEndInv = date('Y-03-05', strtotime($invoice->inv_date));
                                    break;
                                case '1':
                                    $estimatedEndInv = date('Y-03-05', strtotime($invoice->inv_date));
                                    break;
                                default:
                                    # code...
                                    break;
                            }
                        }
                    break;
                    default:
                        # code...
                    break;
                }

            }else{
                $estimatedEndInv = date('Y-m-01', strtotime($invoice->inv_date." +".$ctrInv->continv_period." months"));
            }
            //echo $bln.' - '.$tgl.' - '.$tgl2.' - '.$this->invStartDate.' - '.$estimatedEndInv;
            //f($this->invStartDate >= $estimatedEndInv){
                // suda lewat
                //$result = true;
                //echo 'a';
            //}else{
           //     echo 'b';
            //}
            //die();
            //echo $bln.' - '.$this->invStartDate.' - '.$estimatedEndInv.' - '.$periode.' - '.$tgl.' - '.$tgl2;
            //die();
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
        $invoice = null;
        foreach ($period_classifications as $pval) {
            if(in_array($currentmonth, $pval)){
                $first = reset($pval);
                $last = end($pval);
                $this->monthGapPrev = $currentmonth - $first;
                $this->monthGapNext = $last - $currentmonth;

                // cari invoice yg ada dalam gap waktu ini
                $invoice = TrInvoice::where('contr_id', $this->contract->id)->where('invtp_id',$this->invtp->id)->whereBetween(\DB::raw('EXTRACT(month from inv_date)'), [$first, $last])->where('inv_iscancel',0)->first();
                // CTT: sblmnya detect where is posted = 1, sementara dihilangin dlu biar kedetect inv sblmnya
            }
        }
         //echo "<br>INVOICE:<br>".$invoice."<br><br>";
         //echo "<br>MONTH GAP : ".$this->monthGapNext."<br>";
         //echo $first.' - '.$last;
         //die();
        if(empty($invoice)){
            // inv kosong dalam gap waktu periode, allow insert
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

    public function getCalc()
    {
        if($this->costDetail->cost_id == 1){
            return new ElectricityCalc();
        }else if($this->costDetail->cost_id == 2){
            return new WaterCalc();
        }
        return new GeneralCalc();
    }
}