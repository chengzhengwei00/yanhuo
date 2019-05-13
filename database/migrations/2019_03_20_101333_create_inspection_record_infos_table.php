<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInspectionRecordInfosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('inspection_record_infos', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('task_id');
            $table->string('sku');
            $table->text('rate_container');
            $table->string('bar_code');
            $table->string('outside_bar_code');
            $table->string('net_weight');
            $table->string('packing_size');
            $table->string('pic');
            $table->string('upload_pic');
            $table->text('note');
            $table->timestamps();
            $table->engine = 'InnoDB';
            $table->charset = 'utf8';
            $table->collation = 'utf8_unicode_ci';
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('inspection_record_infos');
    }
}
