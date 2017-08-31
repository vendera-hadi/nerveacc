<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrApPaymentDetail extends Model
{
	protected $table ='tr_ap_payment_dtl';
   	protected $fillable =['aphdr_id','appaym_id','amount'];

   	public function header()
   	{
   		return $this->belongsTo('App\Models\TrApPaymentHeader','appaym_id');
   	}

   	public function apheader()
   	{
   		return $this->belongsTo('App\Models\TrApHeader','aphdr_id');	
   	}
}