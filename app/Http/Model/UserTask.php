<?php

namespace App\Http\Model;

use Illuminate\Database\Eloquent\Model;

class UserTask extends Model
{
    //
    public function Task()
    {
        return $this->belongsTo('App\Http\Model\Task');
    }
    public function Contract()
    {
        return $this->belongsTo('App\Http\Model\Contract');
    }
}
