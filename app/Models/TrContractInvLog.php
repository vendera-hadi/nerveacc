<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrContractInvLog extends Model
{
    protected $table ='tr_cont_invlog';
    public $timestamps = false;
    protected $fillable = ['continv_amount','contr_id','invtp_id','costd_id'];
}
