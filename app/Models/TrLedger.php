<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrLedger extends Model
{
   protected $table ='tr_ledger';
   protected $fillable =['ledg_id','ledge_fisyear','ledg_number','ledg_date','ledg_refno','ledg_debit','ledg_credit','legd_description','coa_year','coa_code','dept_code','jour_type_id','created_by','updated_by'];
}
