<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNeracaFmtsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('neraca_fmt', function (Blueprint $table) {
            $table->increments('id');
            $table->string('kodefmt',20);
            $table->char('coa_code',20);
            $table->string('neraca_desc',150);
            $table->char('neraca_header',1);
            $table->char('neraca_variable',5);
            $table->string('neraca_rumus',200);
            $table->integer('neraca_space');
            $table->char('neraca_line',1);      
            $table->char('coa_code2',20);
            $table->string('neraca_desc2',150);
            $table->char('neraca_header2',1);
            $table->char('neraca_variable2',5);
            $table->string('neraca_rumus2',200);
            $table->integer('neraca_space2');
            $table->char('neraca_line2',1);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('neraca_fmt');
    }
}
