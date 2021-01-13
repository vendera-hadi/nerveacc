<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLunasPayment extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tr_invoice_paymhdr', function (Blueprint $table) {
            $table->integer('lunas')->nullable();
            $table->decimal('lebih_pembayaran', 12, 2)->nullable();
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
            $table->dropColumn('lunas');
            $table->dropColumn('lebih_pembayaran');
        });
    }
}
