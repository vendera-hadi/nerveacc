<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AkrualInv extends Model
{
	protected $table ='akrual_inv';
	protected $fillable =['inv_id','inv_number','inv_date','inv_amount','potong_perbulan','coa_code','total_potong','log_potong','prorate_amount','last_status','last_process'];

}