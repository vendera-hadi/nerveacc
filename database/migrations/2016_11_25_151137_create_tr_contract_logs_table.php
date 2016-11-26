<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTrContractLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tr_contract_log', function (Blueprint $table) {
            $table->increments('id');
            $table->string('contlog_code', 15);
            $table->string('contlog_no', 15);
            $table->date('contlog_startdate');
            $table->date('contlog_enddate');
            $table->string('contlog_bast_date', 20)->nullable();
            $table->string('contlog_bast_by', 20)->nullable();
            $table->string('contr_note', 150)->nullable();
            $table->integer('contr_id');
            $table->integer('tenan_id');
            $table->integer('viracc_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tr_contract_log');
    }
}
