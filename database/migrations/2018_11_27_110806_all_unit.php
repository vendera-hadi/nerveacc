<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AllUnit extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('all_unit', function (Blueprint $table) {
            $table->increments('id');
            $table->string('unit_code', 15);
            $table->decimal('unit_sqrt', 6, 2);
            $table->string('va_utilities', 255);
            $table->string('va_maintenance', 255);
            $table->string('meter_air', 255);
            $table->string('meter_listrik', 255);
            $table->integer('untype_id');
            $table->integer('floor_id');
            $table->boolean('used')->default(0);
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
        Schema::dropIfExists('all_unit');
    }
}
