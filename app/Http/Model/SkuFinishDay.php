<?php
/**
 * Created by PhpStorm.
 * User: 98394
 * Date: 2019/7/17
 * Time: 16:00
 */
namespace App\Http\Model;

use Illuminate\Database\Eloquent\Model;

class SkuFinishDay extends Model
{
    protected $table='sku_finish_days';

    protected $fillable = ['sea_day','advance_day','sku'];

    public $timestamps= false;
}