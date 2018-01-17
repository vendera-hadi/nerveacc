<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMsAssetTypeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ms_asset_types', function (Blueprint $table) {
            $table->increments('id');
            $table->string('jenis_harta');
            $table->string('kelompok_harta');
            $table->integer('masa_manfaat');
            $table->float('garis_lurus');
            $table->float('saldo_menurun');
            $table->float('custom_rule');
            $table->char('debit_coa_code',20);
            $table->char('credit_coa_code',20);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ms_asset_types');
    }
}
