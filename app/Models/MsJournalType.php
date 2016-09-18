<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MsJournalType extends Model
{
   protected $table ='ms_journal_type';
   protected $fillable =['jour_type_id','jour_type_name','jour_type_prefix','jour_type_isactive'];
}
