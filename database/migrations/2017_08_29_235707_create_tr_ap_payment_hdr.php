<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTrApPaymentHdr extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tr_ap_payment_hdr', function (Blueprint $table) {
            $table->increments('id');
            $table->string('payment_code')->unique();
            $table->integer('spl_id');
            $table->date('payment_date');
            $table->double('amount',12,2);
            $table->string('check_no',15)->nullable();
            $table->date('check_date')->nullable();
            $table->string('note',50)->nullable();
            $table->datetime('posting_at')->nullable();
            $table->integer('created_by');
            $table->integer('updated_by');
            $table->integer('paymtp_id')->nullable();
            $table->integer('posting_by')->nullable();
            $table->integer('cashbk_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tr_ap_payment_hdr');
    }
}
