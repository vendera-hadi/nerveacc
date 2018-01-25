<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TrAssetMutations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tr_asset_mutations', function (Blueprint $table) {
            $table->increments('id');
            $table->bigInteger('asset_id');
            $table->string('kode_induk')->nullable();
            $table->string('cabang')->nullable();
            $table->string('lokasi')->nullable();
            $table->string('area')->nullable();
            $table->string('departemen')->nullable();
            $table->string('user')->nullable();
            $table->string('kondisi')->nullable();
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
        Schema::dropIfExists('tr_asset_mutations');
    }
}
