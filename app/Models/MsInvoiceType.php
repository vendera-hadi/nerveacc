<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MsInvoiceType extends Model
{
   protected $table ='ms_invoice_type';
   protected $fillable =['invtp_name','invtp_prefix','invtp_coa_ar','created_by','updated_by'];
}
