<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class LogCancelKwDtl extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('log_cancel_kw_dtl', function (Blueprint $table) {
            $table->increments('id');
            $table->decimal('invpayd_amount',12,2);
            $table->integer('inv_id');
            $table->integer('invpayh_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('log_cancel_kw_dtl');
    }
}
