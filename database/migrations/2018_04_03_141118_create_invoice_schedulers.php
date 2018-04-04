<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInvoiceSchedulers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoice_schedulers', function (Blueprint $table) {
            $table->increments('id');
            $table->date('period_start');
            $table->date('period_end');
            $table->bigInteger('invtp_id');
            $table->bigInteger('contract_id');
            $table->string('status');
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
        Schema::drop('invoice_schedulers');
    }
}
