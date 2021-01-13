<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AllUnit extends Model
{
	protected $table ='all_unit';
	protected $fillable =['unit_code','unit_sqrt','va_utilities','va_maintenance','meter_air','meter_listrik','untype_id','floor_id','used'];

}