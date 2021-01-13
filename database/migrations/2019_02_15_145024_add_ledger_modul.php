<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLedgerModul extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tr_ledger', function (Blueprint $table) {
            $table->string('modulname')->nullable();
            $table->integer('refnumber')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tr_ledger', function (Blueprint $table) {
            $table->dropColumn('modulname');
            $table->dropColumn('refnumber');
        });
    }
}
