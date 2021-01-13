<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Deposit extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('deposit', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('unit_id');
            $table->integer('tenan_id');
            $table->integer('contr_id');
            $table->date('deposit_date');
            $table->string('deposit_number', 20);
            $table->text('deposit_keterangan')->nullable();
            $table->decimal('deposit_amount', 12, 2);
            $table->boolean('deposit_post')->nullable();
            $table->datetime('deposit_posting_at')->nullable();
            $table->integer('deposit_posting_by')->nullable();
            $table->integer('return_id')->nullable();
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
        Schema::dropIfExists('deposit');
    }
}
