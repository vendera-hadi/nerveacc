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
            $table->date('contr_bast_date')->nullable();
            $table->string('contr_bast_by', 20)->nullable();
            $table->string('contr_note', 150)->nullable();
            $table->enum('contr_status',['inputed','confirmed','updated'])->default('inputed');  
            $table->char('tenan_id', 36);
            $table->char('mark_id', 36);
            $table->char('renprd_id', 36);
            $table->char('viracc_id', 36);
            $table->char('const_id', 5);
            $table->char('unit_id', 36);
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
