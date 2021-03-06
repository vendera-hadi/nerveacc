<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTrContInvlogsTable extends Migration
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
            $table->integer('contr_id');
            $table->integer('invtp_id');
            $table->integer('costd_id');
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
