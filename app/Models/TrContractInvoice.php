<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrContractInvoice extends Model
{
   protected $table ='tr_contract_invoice';
   protected $fillable =['continv_amount','invtp_id','costd_id','contr_id','continv_period','continv_start_inv','continv_next_inv'];
   public $timestamps  = false;

   public function costdetail()
   {
      return $this->belongsTo('App\Models\MsCostDetail','costd_id');
   }

   public function contract()
   {
      return $this->belongsTo('App\Models\TrContract','contr_id');
   }

   public function invtype()
   {
      return $this->belongsTo('App\Models\MsInvoiceType','invtp_id');
   }
}
