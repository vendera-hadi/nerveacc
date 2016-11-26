<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NeracaFmt extends Model
{
   protected $table ='neraca_fmt';
   protected $fillable =['kodefmt','coa_code','neraca_desc','neraca_header','neraca_variable','neraca_rumus','neraca_space','neraca_line','coa_code2','neraca_desc2','neraca_header2','neraca_variable2','neraca_rumus2','neraca_space2','neraca_line2'];
   public $timestamps  = false;
}
