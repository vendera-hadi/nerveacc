<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AutoreminderSpTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('autoreminder_sp', function (Blueprint $table) {
            $table->increments('id');
            $table->bigInteger('tenan_id');
            $table->bigInteger('contract_id');
            $table->smallInteger('month');
            $table->integer('year');
            $table->boolean('sp1')->default(false);
            $table->boolean('sp2')->default(false);
            $table->boolean('sent')->default(false);
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
        Schema::dropIfExists('autoreminder_sp');
    }
}
