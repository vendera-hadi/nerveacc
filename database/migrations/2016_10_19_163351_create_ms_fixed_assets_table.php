<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMsFixedAssetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ms_fixed_asset', function (Blueprint $table) {
            $table->increments('id');
            $table->string('fixas_name', 50);
            $table->date('fixas_aqc_date');
            $table->date('fixas_use_date');
            $table->decimal('fixas_price', 12, 2);
            $table->decimal('fixas_residu', 10, 2);
            $table->integer('fixas_age');
            $table->string('fixas_supplier', 50);
            $table->string('fixas_pono', 20);
            $table->decimal('fixas_total_depr', 12, 2);
            $table->char('fixas_dbcoa_code', 10);
            $table->string('fixas_dbcoa_name', 50);
            $table->string('fixas_dbcoa_desc', 100);
            $table->char('fixas_crcoa_code', 10);
            $table->string('fixas_crcoa_name', 50);
            $table->string('fixas_crcoa_desc', 100);
            $table->boolean('fixas_isdelete')->default(0);
            $table->integer('catas_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ms_fixed_asset');
    }
}
