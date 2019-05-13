<?php

namespace App\Http\Model;

use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    //
    function user(){
        return $this->belongsTo('App\User', 'foreign_key');
    }
    function contract(){
        return $this->belongsTo('App\Http\Model\Contract', 'foreign_key');
    }
}
