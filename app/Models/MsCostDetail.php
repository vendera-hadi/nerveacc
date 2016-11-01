<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MsCostDetail extends Model
{
   protected $table ='ms_cost_detail';
   protected $fillable =['costd_is','cost_id','costd_name','costd_rate','costd_burden','costd_admin','costd_ismeter'];
   public $timestamps  = false;

   public function costitem(){
   		return $this->belongsTo('App\Models\MsCostItem','cost_id');
   }
}
