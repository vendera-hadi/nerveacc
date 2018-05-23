<?php
namespace App\Libs;

use App\Libs\Meter;
use App\Models\MsConfig;
use App\Models\CutoffHistory;
use App\Models\TrMeter;
use Exception;

class ElectricityCalc extends Meter {

    public function __construct()
    {
        $this->bpju = @MsConfig::where('name','ppju')->first()->value;
    }

    public function calculate()
    {
        try{
            //CEK MIN 40 JAM PEMAKAIAN LISTRIK
            $min = 40 * $this->daya * $this->meter_rate;
            $elec_cost = $this->getMeterUsed() *  $this->meter_rate;
            if($elec_cost > $min){
                $meter_cost = $elec_cost;
            }else{
                $meter_cost = $min;
            }
            $bpju_variable = !empty($this->bpju) ? $this->bpju : 0;
            $bpju = ($bpju_variable/100 * $meter_cost);
            // echo "Meter cost $meter_cost<br>";
            // echo "BPJU $bpju<br>";
            $this->meter_cost = $meter_cost;
            $this->bpju_cost = $bpju;
            $subtotal = $meter_cost + $bpju;
            // echo "Subtotal $subtotal<br>";
            // Tambah public area
            if($this->value_type == 'percent'){
                $public_area = $this->percentage / 100 * $subtotal;
            }else{
                $public_area = $this->percentage;
                if(empty($public_area)) $public_area = 0;
            }
            // echo "Public Area $public_area<br>";
            $total = $subtotal + $this->meter_admin + $public_area;
            // echo "Total before grossup $total<br>";
            if(!empty($this->grossup)){
                $grossup_total = $total / 0.9 * 0.1;
                // echo "Grossup $grossup_total<br>";
                $total += $grossup_total;
            }
              // echo "Grandtotal $total<br>";
            return round($total,2);
        }catch(Exception $e){
            return false;
        }
    }

    public function customNote($date_start, $date_end)
    {
        $public_area = $this->percentage;
        if($this->value_type == 'percent') $public_area = $this->percentage." %";
        $note = $this->costDetail->costd_name." : ".date('d/m/Y',strtotime($date_start))." - ".date('d/m/Y',strtotime($date_end))."<br>Awal : ".number_format($this->meter_start,2)."&nbsp;&nbsp;&nbsp; Akhir : ".number_format($this->meter_end,2)."&nbsp;&nbsp;&nbsp; Pakai : ".number_format($this->getMeterUsed(),2)."&nbsp;&nbsp;&nbsp;Abodemen : ".number_format($this->meter_burden,2)."&nbsp;&nbsp;&nbsp;Tarif (/kWh): ".number_format($this->meter_rate,2)."&nbsp;&nbsp;&nbsp;PPJU : ".@$this->bpju."% &nbsp;&nbsp;&nbsp;Beban Bersama : ".$public_area;
        return $note;
    }

    public function insertToMeter()
    {
        $meter = new TrMeter;
        $meter->meter_start = $this->meter_start;
        $meter->meter_end = $this->meter_end;
        $meter->meter_used = $this->getMeterUsed();
        $meter->meter_cost = $this->meter_cost;
        $meter->meter_burden = $this->meter_burden;
        $meter->meter_admin = $this->meter_admin;
        $meter->costd_id= $this->costDetail->id;
        $meter->prdmet_id = 0;
        $meter->contr_id = $this->contract->id;
        $meter->unit_id = $this->meter_unit;
        $meter->other_cost = $this->bpju_cost;
        $meter->total = $this->calculate();
        $meter->save();
        return $meter;
    }

    public function insertToCutoffMeter()
    {
        $log = new CutoffHistory;
        $log->unit_id = $this->meter_unit;
        $log->costd_id = $this->costDetail->id;
        $log->meter_start = $this->meter_start;
        $log->meter_end = $this->meter_end;
        $log->close_date = $this->close_date;
        $log->save();
        return $log;
    }

}