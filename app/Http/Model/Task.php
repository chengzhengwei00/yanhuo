<?php

namespace App\Http\Model;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    //
    public function userTask()
    {
        return $this->hasMany('App\Http\Model\UserTask');
    }
    public function user()
    {
        return $this->belongsTo('App\Http\Model\User');
    }
}
