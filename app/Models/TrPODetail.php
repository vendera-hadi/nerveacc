<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrPODetail extends Model
{
	protected $table ='tr_purchase_order_dtl';
   	protected $fillable =['po_id','note','amount','is_ppn','coa_code','dept_id','ppn_amount','ppn_coa_code','qty'];

   	public function dept()
   	{
   		return $this->belongsTo('App\Models\MsDepartment', 'dept_id');
   	}
}