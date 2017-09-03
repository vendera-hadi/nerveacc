<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TrApPaymentHeader extends Model
{
	use SoftDeletes;

	protected $table ='tr_ap_payment_hdr';
   	protected $fillable =['payment_date','amount','check_no','check_date','note','posting_at','created_by','updated_by','paymtp_id','posting_by','cashbk_id','spl_id'];

   	public function detail()
   	{
   		return $this->hasMany('App\Models\TrApPaymentDetail','appaym_id');
   	}

   	public function supplier()
   	{
   		return $this->belongsTo('App\Models\MsSupplier','spl_id');	
   	}

      public function cashbank()
      {
         return $this->belongsTo('App\Models\MsCashBank','cashbk_id');  
      }
}