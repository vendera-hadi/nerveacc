<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTrContractsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tr_contract', function (Blueprint $table) {
            $table->increments('id');
            $table->string('contr_code', 15);
            $table->string('contr_no', 15);
            $table->date('contr_startdate');
            $table->date('contr_enddate');
            $table->date('contr_bast_date')->nullable();
            $table->string('contr_bast_by', 20)->nullable();
            $table->string('contr_note', 150)->nullable();
            $table->boolean('contr_iscancel')->default(0);
            $table->string('contr_status')->default('inputed');  
            $table->date('contr_cancel_date')->nullable();
            $table->date('contr_terminate_date')->nullable();
            $table->integer('tenan_id');
            $table->integer('mark_id')->nullable();
            $table->integer('viracc_id');
            $table->integer('const_id');
            $table->integer('unit_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tr_contract');
    }
}
