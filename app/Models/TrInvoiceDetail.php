<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrInvoiceDetail extends Model
{
   protected $table ='tr_invoice_detail';
   protected $fillable =['invdt_amount','invdt_note','costd_is','inv_id','meter_id'];
   public $timestamps  = false;
}
