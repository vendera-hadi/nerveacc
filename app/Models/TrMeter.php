<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrMeter extends Model
{
   protected $table ='tr_meter';
   protected $fillable =['meter_id','meter_start','meter_end','meter_used','meter_cost','meter_burden','meter_admin','costd_is','unit_id','prdmet_id'];
   public $timestamps  = false;
}
