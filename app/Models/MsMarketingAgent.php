<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MsMarketingAgent extends Model
{
   protected $table ='ms_marketing_agent';
   protected $fillable =['mark_id','mark_code','mark_name','mark_address','mark_phone','mark_isactive','created_by','updated_by'];
}
