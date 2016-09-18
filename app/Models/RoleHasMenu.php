<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoleHasMenu extends Model
{
    protected $fillable =['role_id','menu_id'];
}
