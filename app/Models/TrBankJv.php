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
}
