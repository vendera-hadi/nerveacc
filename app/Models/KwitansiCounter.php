<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KwitansiCounter extends Model
{
	protected $table ='kwitansi_counter';
	protected $fillable =['tahun','bulan','last_counter'];

}