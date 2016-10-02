<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMsFloorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ms_floor', function (Blueprint $table) {
            $table->increments('id');
            $table->char('floor_id',36)->unique();
            $table->string('floor_name',15);
            $table->string('created_by',15);
            $table->string('updated_by',15);
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
        Schema::dropIfExists('ms_floor');
    }
}
