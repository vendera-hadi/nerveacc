<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MsCompany extends Model
{
   protected $table ='ms_company';
   protected $fillable =['comp_name','comp_address','comp_phone','comp_fax','comp_sign_inv_name','comp_build_insurance','comp_npp_insurance','comp_materai1','comp_materai1_amount','comp_materai2','comp_materai2_amount','cashbk_id','comp_image','logo','comp_sign_position','title','comp_sqrt'];
   public $timestamps  = false;

   public function MsCashbank(){
   		return $this->hasOne('App\Models\MsCashBank','id','cashbk_id');
   }
}
