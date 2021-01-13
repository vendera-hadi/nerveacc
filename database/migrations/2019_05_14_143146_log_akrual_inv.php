<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class LogAkrualInv extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('log_akrual_inv', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('inv_id');
            $table->string('inv_number', 20);
            $table->date('inv_date');
            $table->decimal('inv_amount', 12, 2);
            $table->date('process_date');
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
        Schema::dropIfExists('log_akrual_inv');
    }
}
