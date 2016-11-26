<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCutoffHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cutoff_history', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('unit_id');
            $table->decimal('meter_start',10,2);
            $table->decimal('meter_end',10,2);
            $table->date('close_date');
            $table->integer('costd_is');
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
        Schema::dropIfExists('cutoff_history');
    }
}
