<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMsUnitOwnersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ms_unit_owner', function (Blueprint $table) {
            $table->increments('id');
            $table->date('unitow_start_date')->nullable();
            $table->integer('unit_id');
            $table->integer('tenan_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ms_unit_owner');
    }
}
