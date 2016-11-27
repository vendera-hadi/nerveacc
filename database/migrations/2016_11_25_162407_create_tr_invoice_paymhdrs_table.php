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
            $table->string('invpayh_checkno',15)->nullable();
            $table->date('invpayh_giro')->nullable();
            $table->string('invpayh_note',50)->nullable();
            $table->decimal('invpayh_amount',12,2);
            $table->decimal('invpayh_settlamt',12,2);
            $table->decimal('invpayh_adjustamt',12,2);
            $table->boolean('invpayh_post')->default(0);
            $table->integer('created_by');
            $table->integer('updated_by');
            $table->datetime('posting_at')->nullable();
            $table->integer('paymtp_code')->nullable();
            $table->integer('posting_by')->nullable();
            $table->integer('cashbk_id')->nullable();
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
