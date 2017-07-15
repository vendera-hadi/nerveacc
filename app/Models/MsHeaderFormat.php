<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MsHeaderFormat extends Model
{
    protected $table ='ms_header_format';
    protected $fillable =['nama','type','created_by'];
}
