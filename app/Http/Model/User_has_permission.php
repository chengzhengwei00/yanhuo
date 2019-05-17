<?php

namespace App\Http\Model;

use Illuminate\Database\Eloquent\Model;

class User_has_permission extends Model
{
    //
    protected $table = 'user_permissions';
    public function Permission()
    {
        return $this->belongsTo('App\Http\Model\Permission');
    }
}
