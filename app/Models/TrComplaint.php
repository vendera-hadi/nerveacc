<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrComplaint extends Model
{
    protected $table ='tr_complaint';
   	protected $fillable =['comtr_no','comtr_date','comtr_note','comtr_handling_date','comtr_handling_by','comtr_finish_date','comtr_handling_note','compl_id','unit_id','created_by','updated_by'];
}
