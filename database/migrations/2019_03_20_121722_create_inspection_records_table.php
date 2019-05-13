<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInspectionRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('inspection_records', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('task_id');
            $table->string('contract_no');
            $table->text('remark');
            $table->smallInteger('is_standard');
            $table->smallInteger('adopt_quantity');
            $table->smallInteger('review_remark');
            $table->smallInteger('status');
            $table->dateTime('inspection_date');
            $table->string('review_status');
            $table->dateTime('review_date');
            $table->string('version');
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
        Schema::dropIfExists('inspection_records');
    }
}
