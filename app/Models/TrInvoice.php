<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrInvoice extends Model
{
   protected $table ='tr_invoice';
   protected $fillable =['tenan_id','inv_number','inv_data','inv_duedate','inv_amount','inv_ppn','inv_ppn_amount','invtp_code','contr_id','inv_post'];
   public $timestamps  = false;
}
