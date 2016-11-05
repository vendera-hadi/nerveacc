<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTrInvoiceDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tr_invoice_detail', function (Blueprint $table) {
            $table->increments('id');
            $table->decimal('invdt_amount', 10, 2);
            $table->string('invdt_note', 200);
            $table->char('costd_is', 36);
            $table->char('inv_id', 36);
            $table->integer('meter_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tr_invoice_detail');
    }
}
