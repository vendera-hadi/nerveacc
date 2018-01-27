<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MsPerawatanAsset extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ms_perawatan_asset', function (Blueprint $table) {
            $table->increments('id');
            $table->bigInteger('asset_id');
            $table->datetime('date');
            $table->string('ref_no')->nullable();
            $table->text('note')->nullable();
            $table->decimal('price', 18, 2)->default(0);
            $table->string('part_no')->nullable();
            $table->string('user')->nullable();
            $table->string('supplier')->nullable();
            $table->string('invoice_no')->nullable();
            $table->string('guarantee_duedate')->nullable();
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
        Schema::dropIfExists('ms_perawatan_asset');
    }
}
