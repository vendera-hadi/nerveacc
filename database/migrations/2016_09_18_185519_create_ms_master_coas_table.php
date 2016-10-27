<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMsMasterCoasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ms_master_coa', function (Blueprint $table) {
            $table->increments('id');
            $table->unique('coa_year');
            $table->unique('coa_code');
            $table->char('coa_year', 4);
            $table->char('coa_code', 10);
            $table->string('coa_name', 100);
            $table->boolean('coa_isparent')->default(0);
            $table->tinyinteger('coa_level');
            $table->char('coa_type', 10);
            $table->decimal('coa_beginning', 18, 2);
            $table->decimal('coa_debit', 18, 2);
            $table->decimal('coa_credit', 18, 2);
            $table->decimal('coa_ending', 18, 2);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ms_master_coa');
    }
}
