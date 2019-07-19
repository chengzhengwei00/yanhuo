<?php

namespace App\Http\Model;

use Illuminate\Database\Eloquent\Model;

class InspectionGroupsUser extends Model
{
    protected $table='inspection_groups_users';

    protected $fillable = ['inspection_group_id','user_id'];

    public $timestamps= false;

    public function inspection_group(){
       return $this->belongsTo('App\Http\Model\InspectionGroup','inspection_group_id');
    }

    public function user(){
        return $this->belongsTo('App\Http\Model\User','user_id');
    }
}
