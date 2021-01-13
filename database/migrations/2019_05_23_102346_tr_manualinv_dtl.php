<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TrManualinvDtl extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tr_manualinv_dtl', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('manual_id');
            $table->text('manual_keterangan');
            $table->decimal('manuald_amount',12,2);
            $table->char('coa_code', 10)->nullable();
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
        Schema::dropIfExists('tr_manualinv_dtl');
    }
}
