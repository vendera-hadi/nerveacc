<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MsPaymentType extends Model
{
   protected $table ='ms_payment_type';
   protected $fillable =['paymtp_code','paymtp_name','created_by','updated_by'];
}
