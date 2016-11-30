<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrInvoice extends Model
{
   protected $table ='tr_invoice';
   protected $fillable =['tenan_id','inv_number','inv_date','inv_duedate','inv_amount','inv_ppn','inv_ppn_amount','inv_outstanding','inv_faktur_no','inv_faktur_date','inv_iscancel','inv_post','invtp_id','contr_id','created_by','updated_by'];
}
