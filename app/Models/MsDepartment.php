<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MsDepartment extends Model
{
   protected $table ='ms_department';
   protected $fillable =['dept_name','dept_isactive'];
}
