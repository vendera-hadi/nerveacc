<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MsCompany extends Model
{
   protected $table ='ms_company';
   protected $fillable =['comp_name','comp_address','comp_phone','comp_fax','comp_sign_inv_name','comp_build_insurance','comp_npp_insurance','cashbk_id'];
   public $timestamps  = false;
}