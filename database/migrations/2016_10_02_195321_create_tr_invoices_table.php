<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTrInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tr_invoice', function (Blueprint $table) {
            $table->increments('id');
            $table->char('inv_id', 36)->unique();
            $table->char('tenan_id', 36);
            $table->string('inv_number', 20);
            $table->date('inv_date');
            $table->date('inv_duedate');
            $table->decimal('inv_amount', 12, 2);
            $table->decimal('inv_ppn', 5, 2);
            $table->decimal('inv_ppn_amount', 12, 2);
            $table->char('invtp_code', 5);
            $table->char('contr_id', 36);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tr_invoice');
    }
}
