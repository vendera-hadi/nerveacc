<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MsUnitType extends Model
{
   protected $table ='ms_unit_type';
   protected $fillable =['untype_name','untype_isactive','created_by','updated_by'];
}
