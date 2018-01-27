<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddingExtraParamToMsAssets extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ms_assets', function (Blueprint $table) {
            $table->bigInteger('group_account_id')->nullable();
            $table->char('aktiva_coa_code',20)->nullable();
            $table->string('supplier_id')->nullable();
            $table->string('po_no')->nullable();
            $table->string('kode_induk')->nullable();
            $table->string('cabang')->nullable();
            $table->string('lokasi')->nullable();
            $table->string('area')->nullable();
            $table->string('departemen')->nullable();
            $table->string('user')->nullable();
            $table->string('kondisi')->nullable();
            $table->text('image')->nullable();
            $table->text('keterangan')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ms_assets', function (Blueprint $table) {
            $table->dropColumn('group_account_id');
            $table->dropColumn('aktiva_coa_code');
            $table->dropColumn('supplier_id');
            $table->dropColumn('po_no');
            $table->dropColumn('kode_induk');
            $table->dropColumn('cabang');
            $table->dropColumn('lokasi');
            $table->dropColumn('area');
            $table->dropColumn('departemen');
            $table->dropColumn('user');
            $table->dropColumn('kondisi');
            $table->dropColumn('image');
            $table->dropColumn('keterangan');
        });
    }
}
