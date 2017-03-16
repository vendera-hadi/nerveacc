<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPaymentCodeToPaymentHdr extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tr_invoice_paymhdr', function (Blueprint $table) {
            $table->string('no_kwitansi')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tr_invoice_paymhdr', function (Blueprint $table) {
            $table->dropColumn('no_kwitansi');
        });
    }
}
