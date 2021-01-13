<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ManualDtl extends Model
{
	protected $table ='tr_manualinv_dtl';
	protected $fillable =['manual_id','manual_keterangan','manuald_amount','coa_code'];

}