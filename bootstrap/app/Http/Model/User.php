<?php

namespace App\Http\Model;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    //
    public function permissions()
    {
        return $this->belongsToMany('App\Http\Model\Permission','user_has_permissions');
    }
    public function UserTask()
    {
        return $this->belongsToMany('App\Http\Model\Contract', 'user_tasks', 'user_id', 'contract_id')->withPivot('count');
    }
    public function role()
    {
        return $this->belongsToMany('App\Http\Model\Role','user_roles');
    }
}
