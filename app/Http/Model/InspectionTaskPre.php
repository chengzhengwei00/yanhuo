<?php

namespace App\Http\Model;


use Illuminate\Database\Eloquent\Model;


class InspectionTaskPre extends Model
{

    protected $table='inspection_task_pre';

    public function apply_inspection()
    {
        return $this->belongsTo('App\Http\Model\ApplyInspection','apply_inspection_id','id');
    }



}
