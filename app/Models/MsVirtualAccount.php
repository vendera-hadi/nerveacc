<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MsVirtualAccount extends Model
{
   protected $table ='ms_virtual_account';
   protected $fillable =['viracc_id','viracc_no','viracc_name','viracc_isactive','created_by','updated_by'];
}
