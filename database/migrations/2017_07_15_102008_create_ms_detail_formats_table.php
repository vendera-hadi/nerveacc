<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMsDetailFormatsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ms_detail_format', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('formathd_id');
            $table->string('coa_code', 255);
            $table->string('desc', 255)->nullable();
            $table->string('header', 255)->nullable();
            $table->string('variable', 255)->nullable();
            $table->string('formula', 255)->nullable();
            $table->string('linespace', 255)->nullable();
            $table->string('underline', 255)->nullable();
            $table->integer('coloum');
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
        Schema::dropIfExists('ms_detail_format');
    }
}
