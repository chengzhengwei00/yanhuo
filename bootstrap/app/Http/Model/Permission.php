<?php

namespace App\Http\Model;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    //
    public function users()
    {
        return $this->belongsToMany('App\Http\Model\User','user_has_permissions');
    }
    public function role()
    {
        return $this->belongsToMany('App\Http\Model\Role','role_permissions');
    }
}
