<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MsCashBank extends Model
{
   protected $table ='ms_cash_bank';
   protected $fillable =['cashbk_code','cashbk_name','cash_isbank','curr_code'];
   public $timestamps  = false;
}
