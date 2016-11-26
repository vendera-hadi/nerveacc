<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MsGroupAccnDtl extends Model
{
   protected $table ='ms_group_accn_dtl';
   protected $fillable =['grpaccn_id','coa_code'];
   public $timestamps  = false;
}
