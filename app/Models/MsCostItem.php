<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MsCostItem extends Model
{
   protected $table ='ms_cost_item';
   protected $fillable =['cost_code','cost_name','cost_isactive','created_by','updated_by','is_service_charge','is_insurance','is_sinking_fund'];
}
