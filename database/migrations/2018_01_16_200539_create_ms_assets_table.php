<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMsAssetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ms_assets', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->integer('ms_asset_type_id');
            $table->string('depreciation_type');
            $table->datetime('date');
            $table->decimal('price', 18, 2)->default(0);
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
        Schema::dropIfExists('ms_assets');
    }
}
