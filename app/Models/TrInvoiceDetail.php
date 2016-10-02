<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrInvoiceDetail extends Model
{
   protected $table ='tr_invoice_detail';
   protected $fillable =['invdt_id','invdt_amount','invdt_note','costd_is','inv_id'];
   public $timestamps  = false;
}
