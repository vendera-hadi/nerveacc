<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReminderD extends Model
{
	protected $table ='reminder_details';
	protected $fillable =['reminderh_id','inv_id','denda_days','denda_amount'];

}