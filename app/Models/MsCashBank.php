<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MsCashBank extends Model
{
   protected $table ='ms_cash_bank';
   protected $fillable =['cashbk_name','cashbk_isbank','cashbk_accn_no','coa_code','curr_code','created_by','updated_by'];
}
