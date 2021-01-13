<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Numcounter extends Model
{
	protected $table ='numcounter';
	protected $fillable =['numtype','tahun','bulan','last_counter'];

}