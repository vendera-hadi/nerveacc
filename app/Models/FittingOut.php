<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FittingOut extends Model
{
	protected $table ='tr_fitting_out';
	protected $fillable =['fit_id','out_number','out_date','out_amount','out_keterangan','out_refno','out_post','posting_at','created_by','updated_by','bank_id'];

}