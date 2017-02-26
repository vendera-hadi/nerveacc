<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MsConfig extends Model
{
   protected $table ='ms_config';
   protected $fillable =['name','desc','value'];

}