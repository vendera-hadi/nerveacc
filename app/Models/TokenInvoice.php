<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TokenInvoice extends Model
{
    protected $fillable = ['inv_date','inv_no','meter_no','cust_name','no_unit','location','tariff_index','daya','total_pay','slab_cost','water_cost','gas_cost','admin_cost','materai_cost','bpju','ppn','token_cost','total_kwh','token','inv_post','trx_id'];
}