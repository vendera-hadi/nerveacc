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
            $table->string('coa_code', 255)->nullable();
            $table->string('desc', 255)->nullable();
            $table->string('header', 255)->nullable();
            $table->string('variable', 255)->nullable();
            $table->string('formula', 255)->nullable();
            $table->integer('linespace')->default(0);
            $table->boolean('underline')->default(0);
            $table->boolean('hide')->default(0);
            $table->integer('column');
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
