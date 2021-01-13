<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Numcounter extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('numcounter', function (Blueprint $table) {
            $table->increments('id');
            $table->string('numtype', 10);
            $table->integer('tahun');
            $table->integer('bulan');
            $table->integer('last_counter');
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
        Schema::dropIfExists('numcounter');
    }
}
