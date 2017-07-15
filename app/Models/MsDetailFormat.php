<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MsDetailFormat extends Model
{
    protected $table ='ms_detail_format';
    protected $fillable =['formathd_id','coa_code','desc','header','variable','formula','linespace','underline','column'];
}
