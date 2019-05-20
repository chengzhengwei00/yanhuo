<?php

namespace App\Http\Model;

use Illuminate\Database\Eloquent\Model;

class UserSchedule extends Model
{
    //
    public function Contract()
    {
        return $this->belongsTo('App\Http\Model\Contract');
    }


    function user(){
        return $this->belongsTo('App\User');
    }
}
