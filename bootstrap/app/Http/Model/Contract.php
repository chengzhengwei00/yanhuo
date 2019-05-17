<?php

namespace App\Http\Model;

use Illuminate\Database\Eloquent\Model;

class Contract extends Model
{
    //
    function user()
    {
        return $this->belongsToMany('App\Http\Model\User', 'user_tasks', 'contract_id', 'user_id')->withPivot('count');
    }
    function userSchedule()
    {
        return $this->hasMany('App\Http\Model\UserSchedule', 'contract_id', 'id');
    }
}
