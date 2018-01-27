<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class TrAssetMutation extends Model
{
   protected $table ='tr_asset_mutations';
   protected $fillable =['asset_id','kode_induk','cabang','lokasi','area','departemen','user','kondisi'];

   public function asset()
   {
      return $this->belongsTo('App\Models\MsAsset','asset_id');
   }

}