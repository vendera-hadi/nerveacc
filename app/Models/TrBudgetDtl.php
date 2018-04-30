<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrBudgetDtl extends Model
{
   protected $table ='tr_budget_dtl';
   protected $fillable =['budget_id','coa_code','jan','feb','mar','apr','may','jun','jul','aug','sep','okt','nov','des'];
}