<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTrInvoiceJournalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tr_invoice_journal', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('inv_id');
            $table->string('invjour_voucher',15);
            $table->date('invjour_date');
            $table->string('invjour_note',50)->nullable();
            $table->char('coa_code',10);
            $table->decimal('invjour_debit',12,2);
            $table->decimal('invjour_credit',12,2);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tr_invoice_journal');
    }
}
