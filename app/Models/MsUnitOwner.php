<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MsUnitOwner extends Model
{
   use SoftDeletes;

   protected $table ='ms_unit_owner';
   protected $fillable =['unitow_id','unitow_start_date','unit_id','tenan_id'];
   protected $dates = ['deleted_at'];
   public $timestamps  = false;

   public function tenant()
   {
      return $this->belongsTo('App\Models\MsTenant','tenan_id');
   }

   public function tenantWT()
   {
      return $this->belongsTo('App\Models\MsTenant','tenan_id')->withTrashed();
   }
}
