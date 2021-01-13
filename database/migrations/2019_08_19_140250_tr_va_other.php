<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TrVaOther extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tr_va_other', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('unit_id');
            $table->integer('tenan_id');
            $table->date('va_date');
            $table->decimal('va_amount',12,2);
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
        Schema::dropIfExists('tr_va_other');
    }
}
