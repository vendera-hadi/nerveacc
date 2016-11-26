<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MsFloor extends Model
{
   protected $table ='ms_floor';
   protected $fillable =['floor_name','created_by','updated_by'];
}
