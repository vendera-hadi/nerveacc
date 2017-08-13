<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrBankJv extends Model
{
    protected $table ='tr_bankjv';
    protected $fillable = ['trbank_id','coa_code','debit','credit','note','dept_id'];

    public function header()
    {
    	return $this->belongsTo('App\Models\TrBank','trbank_id');
    }

    public function coa()
    {
    	return $this->belongsTo('App\Models\MsMasterCoa','coa_code','coa_code');
    }

    public function dept()
    {
    	return $this->belongsTo('App\Models\MsDepartment','dept_id');
    }

    public function scopeDebit($query)
    {
    	return $query->where('debit','>',0)->orderBy('id','desc');
    }

    public function scopeCredit($query)
    {
    	return $query->where('credit','>',0)->orderBy('id','desc');
    }
}
