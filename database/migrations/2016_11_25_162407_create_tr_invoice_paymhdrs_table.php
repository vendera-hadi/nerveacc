<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTrInvoicePaymhdrsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tr_invoice_paymhdr', function (Blueprint $table) {
            $table->increments('id');
            $table->date('invpayh_date');
            $table->string('invpayh_checkno',15);
            $table->date('invpayh_giro');
            $table->string('invpayh_note',50);
            $table->decimal('invpayh_amount',12,2);
            $table->decimal('invpayh_settlamt',12,2);
            $table->decimal('invpayh_adjustamt',12,2);
            $table->boolean('invpayh_post')->default(0);
            $table->integer('created_by');
            $table->integer('updated_by');
            $table->datetime('posting_at');
            $table->integer('paymtp_code');
            $table->integer('posting_by');
            $table->integer('cashbk_id');
            $table->integer('contr_id');
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
        Schema::dropIfExists('tr_invoice_paymhdr');
    }
}
