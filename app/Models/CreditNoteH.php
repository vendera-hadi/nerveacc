<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CreditNoteH extends Model
{
	protected $table ='tr_creditnote_hdr';
	protected $fillable =['creditnote_date','creditnote_number','creditnote_keterangan','creditnote_post','posting_at','posting_by','unit_id','inv_id','total_amt'];

	public function CreditNoteD(){
		return $this->hasMany('App\Models\CreditNoteD','creditnote_hdr_id');
	}
}
