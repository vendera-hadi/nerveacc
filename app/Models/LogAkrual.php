<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogAkrual extends Model
{
	protected $table ='log_akrual_inv';
	protected $fillable =['inv_id','inv_number','inv_date','inv_amount','process_date'];

}