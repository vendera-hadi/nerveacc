<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogKwCancelDtl extends Model
{
   protected $table ='log_kw_cancel_dtl';
   protected $fillable =['invpayd_amount','inv_id','invpayh_id','last_outstanding'];
   public $timestamps  = false;

   public function TrInvoice(){
         return $this->belongsTo('App\Models\TrInvoice','inv_id');
   }

   public function paymenthdr(){
      return $this->belongsTo('App\Models\LogKwCancelHdr','invpayh_id');
   }
}