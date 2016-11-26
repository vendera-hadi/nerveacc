<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTrPeriodMetersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tr_period_meter', function (Blueprint $table) {
            $table->increments('id');
            $table->char('prdmet_id', 36)->unique();
            $table->date('prdmet_start_date');
            $table->date('prdmet_end_date');
            $table->date('prd_billing_date');
            $table->integer('created_by');
            $table->integer('updated_by');
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
        Schema::dropIfExists('tr_period_meter');
    }
}
