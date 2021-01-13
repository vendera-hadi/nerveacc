<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ManualType extends Model
{
	protected $table ='ms_manual_type';
	protected $fillable =['name','name_detail','amount'];

}