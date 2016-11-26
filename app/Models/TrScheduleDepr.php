<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrScheduleDepr extends Model
{
   protected $table ='tr_schedule_depr';
   protected $fillable =['schdep_journal','schdep_date','schdep_amount','schdep_accum','schdep_gldate','fixas_code'];
   public $timestamps  = false;
}
