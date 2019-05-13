<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInspectionOtherRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('inspection_other_records', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('task_id');
            $table->string('sku');
            $table->text('specification');
            $table->string('is_standard');
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
        Schema::dropIfExists('inspection_other_records');
    }
}
