<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateContractStandardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contract_standards', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('contract_id');
            $table->text('description');
            $table->string('update_username');
            $table->text('pic');
            $table->smallInteger('is_applicable');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('contract_standards');
    }
}
