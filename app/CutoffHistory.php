<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CutoffHistory extends Model
{
   protected $table ='cutoff_history';
   protected $fillable =['unit_id','meter_start','meter_end','close_date','costd_is'];
}
