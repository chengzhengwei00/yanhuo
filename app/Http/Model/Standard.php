<?php

namespace App\Http\Model;
use Illuminate\Database\Eloquent\Model;

class Standard extends Model
{
    //
    public function post()
    {
        return $this->belongsTo('Http\Model\Contract');
    }
}
