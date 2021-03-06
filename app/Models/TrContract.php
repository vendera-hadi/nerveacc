<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrContract extends Model
{
   protected $table ='tr_contract';
   public $timestamps = false;
   protected $fillable =['contr_code','contr_no','contr_startdate','contr_enddate','contr_bast_date','contr_bast_by','contr_note','contr_status','contr_cancel_date','contr_terminate_date','contr_iscancel','tenan_id','mark_id','renprd_id','viracc_id','unit_id','created_by','updated_by','created_at','updated_at'];

   public function TrInvoice(){
   		return $this->hasMany('App\Models\TrInvoice','contr_id');
   }
   public function MsTenant(){
   		return $this->belongsTo('App\Models\MsTenant','tenan_id');
   }
   public function MsTenantWT(){
                return $this->belongsTo('App\Models\MsTenant','tenan_id')->withTrashed();
   }
   public function MsUnit(){
   		return $this->belongsTo('App\Models\MsUnit','unit_id');
   }
   public function contractInv(){
         return $this->hasMany('App\Models\TrContractInvoice','contr_id');
   }
   public function unitowner(){
         return $this->belongsTo('App\Models\MsUnitOwner','unit_id','unit_id');
   }
   public function creator(){
         return $this->belongsTo('App\Models\User','created_by');
   }
   public function updater(){
         return $this->belongsTo('App\Models\User','updated_by');
   }
}
