<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrContractInvoice extends Model
{
   protected $table ='tr_contract_invoice';
   protected $fillable =['continv_amount','invtp_code','costd_is','contr_id','continv_period','continv_start_inv','continv_next_inv'];
   public $timestamps  = false;
}
