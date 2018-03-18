<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MsCostDetail extends Model
{
   protected $table ='ms_cost_detail';
   protected $fillable =['cost_id','costd_name','costd_unit','costd_rate','costd_burden','costd_admin','costd_ismeter','daya','percentage','value_type','grossup_pph'];
   public $timestamps  = false;

   public function costitem(){
   		return $this->belongsTo('App\Models\MsCostItem','cost_id');
   }
}
