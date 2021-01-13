<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TrDendaPayment extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tr_denda_payment', function (Blueprint $table) {
            $table->increments('id');
            $table->string('denda_number',30);
            $table->date('denda_date');
            $table->decimal('denda_amount',12,2);
            $table->integer('unit_id');
            $table->integer('tenan_id');
            $table->integer('reminderh_id');
            $table->integer('bank_id');
            $table->integer('status_void');
            $table->text('denda_keterangan')->nullable();
            $table->integer('posting');
            $table->datetime('posting_at');
            $table->integer('posting_by');
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
        Schema::dropIfExists('tr_denda_payment');
    }
}
