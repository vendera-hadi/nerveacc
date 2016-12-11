<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrInvoice extends Model
{
   protected $table ='tr_invoice';
   protected $fillable =['tenan_id','inv_number','inv_date','inv_duedate','inv_amount','inv_ppn','inv_ppn_amount','inv_outstanding','inv_faktur_no','inv_faktur_date','inv_iscancel','inv_post','invtp_id','contr_id','created_by','updated_by'];

   public function MsTenant(){
   		return $this->belongsTo('App\Models\MsTenant','tenan_id');
   }
   public function TrInvoiceDetail(){
   		return $this->hasMany('App\Models\TrInvoiceDetail','tenan_id');
   }
   public function InvoiceType(){
   		return $this->belongsTo('App\Models\MsInvoiceType','invtp_id');
   }
   public function TrContract(){
         return $this->belongsTo('App\Models\TrContract','contr_id');
   }
}
