<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MsAsuransiAsset extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ms_asuransi_asset', function (Blueprint $table) {
            $table->increments('id');
            $table->bigInteger('asset_id');
            $table->string('polis_no')->nullable();
            $table->string('company')->nullable();
            $table->datetime('start_date')->nullable();
            $table->datetime('end_date')->nullable();
            $table->decimal('contribution_value', 18, 2)->default(0);
            $table->decimal('premi', 18, 2)->default(0);
            $table->string('ref_no')->nullable();
            $table->text('note')->nullable();
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
        Schema::dropIfExists('ms_asuransi_asset');
    }
}
