<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrDendaPayment extends Model
{
	protected $table ='tr_denda_payment';
	protected $fillable =['denda_number','denda_date','denda_amount','unit_id','tenan_id','reminderh_id','bank_id','status_void','denda_keterangan','posting','posting_at','posting_by'];

	public function tenant(){
         return $this->belongsTo('App\Models\MsTenant','tenan_id');
   	}

   	public function unit(){
         return $this->belongsTo('App\Models\MsUnit','unit_id');
   	}

   	public function reminderh(){
         return $this->belongsTo('App\Models\ReminderH','reminderh_id');
   	}

}