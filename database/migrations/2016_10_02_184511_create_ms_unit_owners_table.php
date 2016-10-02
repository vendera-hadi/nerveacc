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
            $table->char('unitow_id',36)->unique();
            $table->date('unitow_start_date');
            $table->char('unit_id',36);
            $table->char('tenan_id',36);
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
