<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTrMetersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tr_meter', function (Blueprint $table) {
            $table->increments('id');
            $table->decimal('meter_start', 10, 2);
            $table->decimal('meter_end', 10, 2);
            $table->decimal('meter_used', 10, 2);
            $table->decimal('meter_cost', 12, 2);
            $table->decimal('meter_burden', 10, 2);
            $table->decimal('meter_admin', 10, 2);
            $table->integer('costd_is');
            $table->integer('contr_id');
            $table->integer('prdmet_id')->nullable();
            $table->integer('unit_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tr_meter');
    }
}
