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

    public function user(){
        return $this->belongsTo('App\Http\Model\User','user_id');
    }
}
