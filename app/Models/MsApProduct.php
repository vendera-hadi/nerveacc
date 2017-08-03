<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MsApProduct extends Model
{
	use SoftDeletes;

	protected $table ='ms_ap_products';
   	protected $fillable =['name','price','coa_code'];

   	public function detail()
   	{
   		return $this->hasMany('App\Models\TrApDetail','aphdr_id');
   	}
}