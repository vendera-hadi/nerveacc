<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMsRentalPeriodsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ms_rental_period', function (Blueprint $table) {
            $table->increments('id');
            $table->unique('renprd_id');
            $table->char('renprd_id', 36);
            $table->string('renprd_name', 30);
            $table->integer('renprd_day');
            $table->string('created_by', 15);
            $table->string('updated_by', 15);
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
        Schema::dropIfExists('ms_rental_period');
    }
}
