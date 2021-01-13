<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrInvoice extends Model
{
   protected $table ='tr_invoice';
   protected $fillable =['tenan_id','inv_number','inv_date','inv_duedate','inv_amount','inv_ppn','inv_ppn_amount','inv_outstanding','inv_faktur_no','inv_faktur_date','inv_iscancel','inv_post','invtp_id','contr_id','created_by','updated_by','footer','label','unit_id','total_excess_payment','current_last_outstanding'];

   public function MsTenant(){
   		return $this->belongsTo('App\Models\MsTenant','tenan_id');
   }
   public function TrInvoiceDetail(){
   		return $this->hasMany('App\Models\TrInvoiceDetail','inv_id');
   }
   public function InvoiceType(){
   		return $this->belongsTo('App\Models\MsInvoiceType','invtp_id');
   }
   public function TrContract(){
         return $this->belongsTo('App\Models\TrContract','contr_id');
   }
   public function paymentdtl()
   {
      return $this->hasMany('App\Models\TrInvoicePaymdtl','inv_id');
   }

   public function unit(){
         return $this->belongsTo('App\Models\MsUnit','unit_id');
   }

}
