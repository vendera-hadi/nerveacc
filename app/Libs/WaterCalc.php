<?php
namespace App\Libs;

use App\Libs\Meter;
use App\Models\CutoffHistory;
use App\Models\TrMeter;
use Exception;

class WaterCalc extends Meter {

    public function calculate()
    {
        try{
            $this->meter_cost = $this->getMeterUsed() * $this->meter_rate;
            $this->total = $this->meter_cost + $this->meter_burden + $this->meter_admin;
            return round($this->total);
        }catch(Exception $e){
            return false;
        }
    }

    public function customNote($date_start, $date_end)
    {
        $note = $this->costDetail->costd_name." : ".date('d/m/Y',strtotime($date_start))." - ".date('d/m/Y',strtotime($date_end))."<br>Meter Awal : ".number_format($this->meter_start,0)."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Meter Akhir : ".number_format($this->meter_end,0)."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Pemakaian : ".number_format($this->getMeterUsed(),0)."<br>Biaya Pemakaian : ".number_format($this->getMeterUsed(),0)." x ".number_format($this->meter_rate,0)."<br>Biaya Beban Tetap Air : ".number_format($this->meter_burden,0)."<br>Biaya Pemeliharaan Meter : ".number_format($this->meter_admin,0);
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
        $meter->other_cost = @$this->bpju_cost ?: 0;
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