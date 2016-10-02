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
            $table->unique('unit_id');
            $table->char('unit_id', 36);
            $table->string('unit_code', 15);
            $table->string('unit_name', 25);
            $table->decimal('unit_sqrt', 6, 2);
            $table->string('unit_virtual_accn', 20);
            $table->boolean('unit_isactive')->default(0);
            $table->string('created_by', 15);
            $table->string('updated_by', 15);
            $table->char('untype_id', 36);
            $table->char('floor_id', 36);
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
