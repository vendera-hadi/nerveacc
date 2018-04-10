<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTenanIdToPayment extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tr_invoice_paymhdr', function (Blueprint $table) {
            $table->dropColumn('contr_id');
            $table->bigInteger('tenan_id')->nullable()->default(0);
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
            $table->dropColumn('tenan_id');
        });
    }
}
