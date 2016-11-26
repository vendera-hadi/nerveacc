<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrCurrencyRate extends Model
{
   protected $table ='tr_currency_rate';
   protected $fillable =['curr_rate_date','curr_rate_value','curr_code'];
}
