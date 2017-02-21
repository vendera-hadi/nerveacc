<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MsUnit extends Model
{
   protected $table ='ms_unit';
   protected $fillable =['unit_id','unit_code','unit_name','unit_sqrt','unit_virtual_accn','unit_isactive','unit_isavailable','created_by','updated_by','untype_id','floor_id','virtual_account','meter_air','meter_listrik'];

   public function MsFloor(){
   		return $this->belongsTo('App\Models\MsFloor','floor_id');
   }

   public function UnitType(){
   		return $this->belongsTo('App\Models\MsUnitType', 'untype_id');
   }
}
