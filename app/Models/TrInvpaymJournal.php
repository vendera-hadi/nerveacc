<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrInvpaymJournal extends Model
{
   protected $table ='tr_invpaym_journal';
   protected $fillable =['ipayjour_date','ipayjour_voucher','ipayjour_note','coa_code','ipayjour_debit','ipayjour_credit','invpayh_id'];
   public $timestamps  = false;
}
