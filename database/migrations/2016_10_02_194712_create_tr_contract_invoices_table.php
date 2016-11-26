<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTrContractInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tr_contract_invoice', function (Blueprint $table) {
            $table->increments('id');
            $table->decimal('continv_amount', 10, 2);
            $table->integer('continv_period')->default(1);
            $table->date('continv_start_inv')->nullable();
            $table->date('continv_next_inv')->nullable();
            $table->integer('contr_id');
            $table->integer('invtp_code');
            $table->integer('costd_is');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tr_contract_invoice');
    }
}
