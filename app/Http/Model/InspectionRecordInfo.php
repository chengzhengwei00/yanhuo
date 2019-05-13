<?php

namespace App\Http\Model;

use Illuminate\Database\Eloquent\Model;

class InspectionRecordInfo extends Model
{
    //
    public function contract()
    {
        return $this->belongsTo('App\Http\Model\Contract','contract_id','id');
    }
    public function user()
    {
        return $this->belongsTo('App\Http\Model\User','user_id','id');
    }
    public function task()
    {
        return $this->belongsTo('App\Http\Model\Task');
    }
}
