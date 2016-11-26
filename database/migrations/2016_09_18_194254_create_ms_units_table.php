<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMsUnitsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ms_unit', function (Blueprint $table) {
            $table->increments('id');
            $table->string('unit_code', 15);
            $table->string('unit_name', 25);
            $table->decimal('unit_sqrt', 6, 2);
            $table->string('unit_virtual_accn', 20);
            $table->boolean('unit_isactive')->default(0);
            $table->integer('created_by');
            $table->integer('updated_by');
            $table->integer('untype_id');
            $table->integer('floor_id');
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
        Schema::dropIfExists('ms_unit');
    }
}
