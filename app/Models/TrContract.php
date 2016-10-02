<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrContract extends Model
{
   protected $table ='tr_contract';
   protected $fillable =['contr_id','contr_parent','contr_code','contr_no','contr_startdate','contr_enddate','contr_bast_date','contr_bast_by','contr_note','tenan_id','mark_id','renprd_id','viracc_id','const_code','unit_id'];
}
