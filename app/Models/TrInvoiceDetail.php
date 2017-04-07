<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrInvoiceDetail extends Model
{
   protected $table ='tr_invoice_detail';
   protected $fillable =['invdt_amount','invdt_note','costd_id','inv_id','meter_id','coa_code'];
   public $timestamps  = false;
}
