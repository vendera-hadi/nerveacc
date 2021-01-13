<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CreditNoteD extends Model
{
	protected $table ='tr_creditnote_dtl';
	protected $fillable =['inv_id','creditnote_hdr_id','coa_code','jurnal_type','inv_amount','credit_amount'];

	public function CreditNoteH(){
	  	return $this->belongsTo('App\Models\CreditNoteH','creditnote_hdr_id');
	}
}