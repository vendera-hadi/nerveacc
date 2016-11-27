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
            $table->integer('tenan_id');
            $table->string('inv_number', 20);
            $table->date('inv_date');
            $table->date('inv_duedate');
            $table->decimal('inv_amount', 12, 2);
            $table->decimal('inv_ppn', 5, 2);
            $table->decimal('inv_ppn_amount', 12, 2);
            $table->decimal('inv_outstanding', 12, 2);
            $table->string('inv_faktur_no',25);
            $table->date('inv_faktur_date');
            $table->boolean('inv_iscancel')->default(false);
            $table->boolean('inv_post')->default(false);
            $table->char('invtp_code', 5);
            $table->integer('contr_id');
            $table->integer('created_by');
            $table->integer('updated_by');
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
        Schema::dropIfExists('tr_invoice');
    }
}
