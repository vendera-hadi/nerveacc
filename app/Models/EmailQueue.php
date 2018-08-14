<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailQueue extends Model
{
	protected $table ='email_queues';
	protected $fillable =['mailclass','to','cc','ref_id','status','sent_at','note'];

}