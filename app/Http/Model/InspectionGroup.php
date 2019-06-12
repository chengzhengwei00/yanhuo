<?php

namespace App\Http\Model;

use Illuminate\Database\Eloquent\Model;

class InspectionGroup extends Model
{
    protected $table='inspection_groups';

    protected $fillable = ['name'];


    public function apply_inspections(){
       return $this->hasMany('App\Http\Model\ApplyInspection','inspection_group_id');
    }

}
