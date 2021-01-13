<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExcessPayment extends Model
{
	protected $table ='excess_payment';
	protected $fillable =['unit_id','total_amount'];

}