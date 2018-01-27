<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class MsPerawatanAsset extends Model
{
   protected $table ='ms_perawatan_asset';
   protected $fillable =['asset_id','date','ref_no','note','price','part_no','user','supplier','invoice_no','guarantee_duedate'];

 }