<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MsUnit extends Model
{
   protected $table ='ms_unit';
   protected $fillable =['unit_id','unit_code','unit_name','unit_sqrt','unit_virtual_accn','unit_isactive','created_by','updated_by','untype_id','floor_id'];
}
