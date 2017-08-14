<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrPOHeader extends Model
{
	protected $table ='tr_purchase_order_hdr';
   	protected $fillable =['spl_id','po_number','po_date','due_date','terms','note','created_by','updated_by'];

   	public function detail()
   	{
   		return $this->hasMany('App\Models\TrPODetail','po_id');
   	}
}