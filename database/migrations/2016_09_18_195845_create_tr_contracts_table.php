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
            $table->unique('contr_id');
            $table->char('contr_id', 36);
            $table->char('contr_parent', 36);
            $table->string('contr_code', 15);
            $table->string('contr_no', 15);
            $table->date('contr_startdate');
            $table->date('contr_enddate');
            $table->date('contr_bast_date');
            $table->string('contr_bast_by', 20);
            $table->string('contr_note', 150);
            $table->char('tenan_id', 36);
            $table->char('mark_id', 36);
            $table->char('renprd_id', 36);
            $table->char('viracc_id', 36);
            $table->char('const_code', 5);
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