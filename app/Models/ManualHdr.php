<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ManualHdr extends Model
{
	protected $table ='tr_manualinv_hdr';
	protected $fillable =['unit_id','tenan_id','manual_number','manual_date','manual_duedate','manual_amount','cashbk_id','manual_type','manual_footer','manual_post','posting_at','created_by','updated_by','manual_refno'];

}