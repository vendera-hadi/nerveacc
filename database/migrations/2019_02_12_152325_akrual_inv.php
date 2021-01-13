<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AkrualInv extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('akrual_inv', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('inv_id');
            $table->string('inv_number', 20);
            $table->date('inv_date');
            $table->decimal('inv_amount', 12, 2);
            $table->decimal('potong_perbulan', 12, 2);
            $table->char('coa_code', 10);
            $table->integer('total_potong');
            $table->integer('log_potong');
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
        Schema::dropIfExists('akrual_inv');
    }
}
