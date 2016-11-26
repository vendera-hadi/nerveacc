<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MsContractStatus extends Model
{
   protected $table ='ms_contract_status';
   protected $fillable =['const_name','const_order','created_by','updated_by'];
}
