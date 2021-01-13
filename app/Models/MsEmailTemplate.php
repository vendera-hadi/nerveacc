<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MsEmailTemplate extends Model
{
    protected $table ='ms_email_templates';
    protected $fillable =['name','view','title','subject','content'];
}