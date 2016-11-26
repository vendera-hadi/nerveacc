<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrInvoiceJournal extends Model
{
   protected $table ='tr_invoice_journal';
   protected $fillable =['inv_id','invjour_voucher','invjour_date','invjour_note','coa_code','invjour_debit','invjour_credit'];
   public $timestamps  = false;
}
