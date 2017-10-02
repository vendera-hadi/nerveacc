<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TrApDetail extends Model
{
	use SoftDeletes;

	protected $table ='tr_ap_invoice_dtl';
   	protected $fillable =['aphdr_id','note','amount','is_ppn','coa_code','dept_id','ppn_amount','ppn_coa_code','qty','coa_type'];
}