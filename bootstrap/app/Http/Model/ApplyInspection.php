<?php

namespace App\Http\Model;

use Illuminate\Database\Eloquent\Model;

class ApplyInspection extends Model
{
    //
    public function user()
    {
        return $this->belongsTo('App\Http\Model\User','apply_user','id');
    }
    public function contract()
    {
        return $this->belongsTo('App\Http\Model\Contract');
    }

}
