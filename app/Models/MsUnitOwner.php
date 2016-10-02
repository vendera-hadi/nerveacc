<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MsUnitOwner extends Model
{
   protected $table ='ms_unit_owner';
   protected $fillable =['unitow_id','unitow_start_date','unit_id','tenan_id'];
   public $timestamps  = false;
}
