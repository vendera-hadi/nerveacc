<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTrScheduleDeprsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tr_schedule_depr', function (Blueprint $table) {
            $table->increments('id');
            $table->string('schdep_journal',15);
            $table->date('schdep_date');
            $table->decimal('schdep_amount',12,2);
            $table->decimal('schdep_accum',12,2);
            $table->datetime('schdep_gldate');
            $table->integer('fixas_code');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tr_schedule_depr');
    }
}
