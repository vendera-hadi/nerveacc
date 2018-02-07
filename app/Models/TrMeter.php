<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrMeter extends Model
{
   protected $table ='tr_meter';
   protected $fillable =['meter_start','meter_end','meter_used','meter_cost','meter_burden','meter_admin','costd_id','contr_id','prdmet_id','unit_id','other_cost','total'];
   public $timestamps  = false;

   public function cost_detail()
   {
      return $this->belongsTo('App\Models\MsCostDetail', 'costd_id');
   }

   public function period_meter()
   {
      return $this->belongsTo('App\Models\TrPeriodMeter', 'prdmet_id');
   }
}
