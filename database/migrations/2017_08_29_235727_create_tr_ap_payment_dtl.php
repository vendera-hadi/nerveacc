<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTrApPaymentDtl extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tr_ap_payment_dtl', function (Blueprint $table) {
            $table->increments('id');
            $table->decimal('amount',12,2);
            $table->integer('aphdr_id');
            $table->integer('appaym_id');
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
        Schema::dropIfExists('tr_ap_payment_dtl');
    }
}
