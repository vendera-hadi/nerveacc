<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MsFixedAsset extends Model
{
   protected $table ='ms_fixed_asset';
   protected $fillable =['fixas_code','fixas_name','fixas_aqc_date','fixas_use_date','fixas_price','fixas_residu','fixas_age','fixas_supplier','fixas_pono','fixas_total_depr','fixas_dbcoa_code','fixas_dbcoa_name','fixas_dbcoa_desc','fixas_crcoa_code','fixas_crcoa_name','fixas_crcoa_desc','fixas_isdelete','catas_id'];
   public $timestamps  = false;
}
