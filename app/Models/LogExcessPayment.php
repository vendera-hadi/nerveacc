<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogExcessPayment extends Model
{
	protected $table ='log_excess_payment';
	protected $fillable =['invpayh_id','excess_amount','unit_id'];

}