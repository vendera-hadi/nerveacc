<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MsComplaint extends Model
{
   protected $table ='ms_complaint';
   protected $fillable =['compl_code','compl_name','compl_isactive','created_by','updated_by'];
}
