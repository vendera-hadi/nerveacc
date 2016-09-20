<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MsTenantType extends Model
{
   protected $table ='ms_tenant_type';
   protected $fillable =['tent_id','tent_name'];
   public $timestamps  = false;
}
