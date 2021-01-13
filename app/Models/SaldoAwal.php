<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaldoAwal extends Model
{
   protected $table ='saldo_awal_cashflow';
   protected $fillable =['year','saldo_awal','created_at'];
   public $timestamps  = false;
}
