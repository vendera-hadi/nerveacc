<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTrCurrencyRatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tr_currency_rate', function (Blueprint $table) {
            $table->increments('id');
            $table->date('curr_rate_date');
            $table->decimal('curr_rate_value', 10, 2);
            $table->integer('curr_code');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tr_currency_rate');
    }
}
