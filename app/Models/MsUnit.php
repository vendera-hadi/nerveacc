<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MsUnit extends Model
{
   protected $table ='ms_unit';
   protected $fillable =['unit_id','unit_name','created_by','updated_by','untype_id'];
}
