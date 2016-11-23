<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTrContInvlog extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tr_cont_invlog', function (Blueprint $table) {
            $table->increments('id');
            $table->decimal('continv_amount',10,2)->default(0);
            $table->string('invtp_code',5);
            $table->integer('costd_is');
            $table->bigInteger('contr_id');
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
        Schema::dropIfExists('tr_cont_invlog');
    }
}
