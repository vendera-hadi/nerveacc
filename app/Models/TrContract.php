<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrContract extends Model
{
   protected $table ='tr_contract';
   public $timestamps = false;
   protected $fillable =['contr_code','contr_no','contr_startdate','contr_enddate','contr_bast_date','contr_bast_by','contr_note','contr_status','contr_cancel_date','contr_terminate_date','contr_iscancel','tenan_id','mark_id','renprd_id','viracc_id','unit_id'];
}
