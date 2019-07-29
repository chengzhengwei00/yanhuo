<?php

namespace App\Http\Model;

use Illuminate\Database\Eloquent\Model;

class InspectionGroup extends Model
{
    protected $table='inspection_groups';

    protected $fillable = ['name','inspection_group_no'];




    public function apply_inspections(){
       return $this->hasMany('App\Http\Model\ApplyInspection','inspection_group_id');
    }

    public function user(){
        return $this->belongsTo('App\Http\Model\User','user_id');
    }

    public function inspection_group_user(){
        return $this->hasMany('App\Http\Model\InspectionGroupsUser','inspection_group_id');
    }
}
