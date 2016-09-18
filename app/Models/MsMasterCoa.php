<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MsMasterCoa extends Model
{
   protected $table ='ms_master_coa';
   protected $fillable =['coa_year','coa_code','coa_name','coa_isparent','coa_level','coa_type','coa_beginning','coa_debit','coa_credit','coa_ending'];
}
