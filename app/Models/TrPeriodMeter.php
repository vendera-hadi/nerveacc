<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrPeriodMeter extends Model
{
   protected $table ='tr_period_meter';
   protected $fillable =['prdmet_id','prdmet_start_date','prdmet_end_date','prd_billing_date','created_by','updated_by'];
}
