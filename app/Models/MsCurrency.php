<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MsCurrency extends Model
{
   protected $table ='ms_currency';
   protected $fillable =['curr_code','curr_name','curr_isactive','created_by','updated_by'];
}
