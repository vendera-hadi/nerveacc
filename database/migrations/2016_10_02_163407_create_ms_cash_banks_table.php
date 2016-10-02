<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMsCashBanksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ms_cash_bank', function (Blueprint $table) {
            $table->increments('id');
            $table->unique('cashbk_code');
            $table->string('cashbk_code',10);
            $table->boolean('cashbk_isbank')->default(0);
            $table->string('cashbk_accn_no',15);
            $table->char('curr_code',3);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ms_cash_bank');
    }
}
