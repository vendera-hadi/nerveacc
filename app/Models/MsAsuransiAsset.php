<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class MsAsuransiAsset extends Model
{
    protected $table ='ms_asuransi_asset';
    protected $fillable =['asset_id','polis_no','company','start_date','end_date','contribution_value','premi','ref_no','note'];
}