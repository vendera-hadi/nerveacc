<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrInvoicePaymhdr extends Model
{
   protected $table ='tr_invoice_paymhdr';
   protected $fillable =['invpayh_date','invpayh_checkno','invpayh_girodate','invpayh_note','invpayh_amount','invpayh_settlamt','invpayh_adjustamt','invpayh_post','created_by','updated_by','posting_at','paymtp_code','posting_by','cashbk_id','contr_id'];

   public function Cashbank(){
   		return $this->belongsTo('App\Models\MsCashBank','cashbk_id');
   }
}
