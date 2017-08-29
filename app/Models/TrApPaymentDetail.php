<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrApPaymentDetail extends Model
{
	protected $table ='tr_ap_invoice_dtl';
   	protected $fillable =['aphdr_id','appaym_id','amount'];

   	public function header()
   	{
   		return $this->belongsTo('App\Models\TrApPaymentHeader','appaym_id');
   	}
}