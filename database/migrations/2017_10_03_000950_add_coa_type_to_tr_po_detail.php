<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCoaTypeToTrPoDetail extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tr_purchase_order_dtl', function (Blueprint $table) {
            $table->enum('coa_type', ['DEBET','KREDIT'])->default('KREDIT');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tr_purchase_order_dtl', function (Blueprint $table) {
            $table->dropColumn('coa_type');
        });
    }
}
