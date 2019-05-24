<?php

namespace App\Http\Model;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    //
    public function permissions()
    {
        return $this->belongsToMany('App\Http\Model\Permission','role_permissions');
    }

    public function users()
    {
        return $this->belongsToMany('App\Http\Model\User','user_roles');
    }
}
