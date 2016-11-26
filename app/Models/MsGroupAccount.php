<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MsGroupAccount extends Model
{
   protected $table ='ms_group_account';
   protected $fillable =['grpaccn_name','created_by','updated_by'];
}
