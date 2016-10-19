<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MsCategoryAsset extends Model
{
   protected $table ='ms_category_asset';
   protected $fillable =['catas_id','catas_name','catas_age','created_by','updated_by'];
}
