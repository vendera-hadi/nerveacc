<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MsTenant extends Model
{
   protected $table ='ms_tenant';
   protected $fillable =['tenan_code','tenan_name','tenan_idno','tenan_phone','tenan_fax','tenan_email','tenan_address','tenan_npwp','tenan_taxname','tenan_tax_address','tenan_isppn','tenan_ispkp','created_by','updated_by','tent_id'];
}
