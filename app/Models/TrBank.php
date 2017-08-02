<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrBank extends Model
{
    protected $table ='tr_bank';
    protected $fillable = ['trbank_no','trbank_date','trbank_group','trbank_in','trbank_out','trbank_girodate','trbank_girono','trbank_charge','trbank_note','trbank_recipient','coa_code','trbank_rekon','trbank_post','posting_at','paymtp_id','cashbk_id','created_by','updated_by'];

    public function detail()
    {
    	return $this->hasMany('App\Models\TrBankJv','trbank_id');
    }

    public function tfdetail()
    {
    	return $this->hasMany('App\Models\TrBankJv','trbank_id')->debit();
    }

    public function wddetail()
    {
    	return $this->hasMany('App\Models\TrBankJv','trbank_id')->credit();
    }
}
