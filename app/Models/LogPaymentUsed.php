<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogPaymentUsed extends Model
{
	protected $table ='log_payment_used';
	protected $fillable =['inv_id','unit_id','used_amount'];

}