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
            $table->string('fixas_code', 15)->unique();
            $table->string('fixas_name', 50);
            $table->date('fixas_aqc_date');
            $table->integer('fixas_age');
            $table->string('fixas_supplier', 50);
            $table->string('fixas_pono', 20);
            $table->decimal('fixas_total_depr', 12, 2);
            $table->boolean('fixas_isdelete')->default(0);
            $table->char('catas_id',2);
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
