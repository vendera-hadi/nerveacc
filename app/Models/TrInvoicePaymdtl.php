<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrInvoicePaymdtl extends Model
{
   protected $table ='tr_invoice_paymdtl';
   protected $fillable =['invpayd_amount','inv_id','invpayh_id'];
   public $timestamps  = false;
}
