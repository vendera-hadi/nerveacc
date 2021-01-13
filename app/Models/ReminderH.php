<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReminderH extends Model
{
	protected $table ='reminder_header';
	protected $fillable =['reminder_no','unit_id','reminder_date','lastsent_date','sent_counter','isi_content','sp_type','denda_total','denda_outstanding','active_tagih','posting','pokok_amount','last_posting'];

}