<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TrCashflowLedger extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tr_cashflow_ledger', function (Blueprint $table) {
            $table->increments('id');
            $table->date('ledg_date');
            $table->string('ledg_refno', 20);
            $table->decimal('ledg_amount', 14, 2);
            $table->string('ledg_description', 255);
            $table->char('coa_code', 10);
            $table->string('modulname',255);
            $table->integer('refnumber');
            $table->string('banktype',2);
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
        Schema::dropIfExists('tr_cashflow_ledger');
    }
}
