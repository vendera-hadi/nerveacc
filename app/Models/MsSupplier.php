<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MsSupplier extends Model
{
   protected $table ='ms_supplier';
   protected $fillable =['spl_id','spl_code','spl_name','spl_address','spl_city','spl_postal_code','spl_phone','spl_fax','spl_cperson','spl_npwp','spl_isactive','created_by','updated_by'];
}
