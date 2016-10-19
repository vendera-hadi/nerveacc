<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MsFixedAsset extends Model
{
   protected $table ='ms_fixed_asset';
   protected $fillable =['fixas_code','fixas_name','fixas_aqc_date','fixas_amount','fixas_age','fixas_supplier','fixas_pono','fixas_total_depr','fixas_isdelete','catas_id'];
   public $timestamps  = false;
}
