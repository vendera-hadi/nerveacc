<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrVaOther extends Model
{
	protected $table ='tr_va_other';
	protected $fillable =['unit_id','tenan_id','va_date','va_amount'];

}