<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrBudgetHdr extends Model
{
   protected $table ='tr_budget_hdr';
   protected $fillable =['tahun','created_by','updated_by'];
}
