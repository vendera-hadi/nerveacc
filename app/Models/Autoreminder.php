<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Autoreminder extends Model
{
  protected $table ='autoreminder_sp';

  public function invoice()
  {
      return $this->hasMany('App\Models\TrInvoice','contr_id','contract_id');
  }
}