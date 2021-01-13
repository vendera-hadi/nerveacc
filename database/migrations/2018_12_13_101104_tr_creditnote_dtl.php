<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TrCreditnoteDtl extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tr_creditnote_dtl', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('inv_id');
            $table->integer('creditnote_hdr_id');
            $table->char('coa_code', 10)->nullable();
            $table->string('jurnal_type',10);
            $table->decimal('inv_amount',12,2);
            $table->decimal('credit_amount',12,2);
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
        Schema::dropIfExists('tr_creditnote_dtl');
    }
}
