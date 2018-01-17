<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MsAssetType extends Model
{
   use SoftDeletes;

   protected $table ='ms_asset_types';
   protected $fillable =['jenis_harta','kelompok_harta','masa_manfaat','garis_lurus','saldo_menurun','custom_rule','debit_coa_code','credit_coa_code'];
   public $timestamps  = false;

}
