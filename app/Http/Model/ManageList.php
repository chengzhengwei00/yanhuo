<?php

namespace App\Http\Model;

use Illuminate\Database\Eloquent\Model;

class ManageList extends Model
{
    //

    protected $table='manage_list';

    public function standard(){
        return $this->hasOne('App\Http\Model\Standard','sku','sku');
    }
}
