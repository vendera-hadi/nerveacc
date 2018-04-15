<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TrApHeader extends Model
{
	use SoftDeletes;

	protected $table ='tr_ap_invoice_hdr';
   	protected $fillable =['spl_id','invoice_date','invoice_duedate','invoice_no','isdp','total','adjust','payment','ppn','posting','note','po_no','apdate','posting_at','created_by','updated_by','terms'];

   	public function detail()
   	{
   		return $this->hasMany('App\Models\TrApDetail','aphdr_id');
   	}

   	public function supplier()
   	{
   		return $this->belongsTo('App\Models\MsSupplier','spl_id');
   	}

      public function po()
      {
         return $this->belongsTo('App\Models\TrPOHeader','po_id');
      }

      public function payment()
       {
         return $this->hasMany('App\Models\TrApPaymentDetail', 'aphdr_id');
       }
}