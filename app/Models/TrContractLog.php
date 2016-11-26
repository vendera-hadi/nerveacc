<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrContractLog extends Model
{
   protected $table ='tr_contract_log';
   protected $fillable =['contlog_code','contlog_no','contlog_startdate','contlog_enddate','contlog_bast_date','contlog_bast_by','contlog_note','contr_id','tenan_id','viracc_id'];
   public $timestamps  = false;
}
