<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FittingIn extends Model
{
	protected $table ='tr_fitting_in';
	protected $fillable =['unit_id','tenan_id','fit_number','fit_date','fit_amount','fit_keterangan','fit_refno','fit_post','posting_at','created_by','updated_by','flag_selesai','cashbk_id'];

}