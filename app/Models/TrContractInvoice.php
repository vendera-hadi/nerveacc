<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrContractInvoice extends Model
{
   protected $table ='tr_contract_invoice';
   protected $fillable =['continv_id','continv_amount','contr_id','invtp_code','costd_is'];
   public $timestamps  = false;
}