<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MsRentalPeriod extends Model
{
   protected $table ='ms_rental_period';
   protected $fillable =['renprd_id','renprd_name','renprd_day','created_by','updated_by'];
}
