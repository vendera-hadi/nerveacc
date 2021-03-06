<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MsGroupAccount extends Model
{
   protected $table ='ms_group_account';
   protected $fillable =['grpaccn_name','created_by','updated_by'];

   public function getDates()
	{
	    return [];
	}

	public function detail()
	{
		return $this->hasMany('App\Models\MsGroupAccnDtl','grpaccn_id');
	}
}
