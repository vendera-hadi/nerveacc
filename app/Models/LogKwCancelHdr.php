<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogKwCancelHdr extends Model
{
	protected $table ='log_kw_cancel_hdr';
	protected $fillable =['invpayh_date','invpayh_checkno','invpayh_girodate','invpayh_note','invpayh_amount','invpayh_settlamt','invpayh_adjustamt','invpayh_post','created_by','updated_by','posting_at','paymtp_code','posting_by','cashbk_id','tenan_id','cancel_by'];

	public function Cashbank(){
   		return $this->belongsTo('App\Models\MsCashBank','cashbk_id');
   }

   public function LogKwCancelDtl(){
   		return $this->hasMany('App\Models\LogKwCancelDtl','invpayh_id');
   }

   public function tenant(){
         return $this->belongsTo('App\Models\MsTenant','tenan_id');
   }

   public function paymentType()
   {
         return $this->belongsTo('App\Models\MsPaymentType','paymtp_code');
   }

}