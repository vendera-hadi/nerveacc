<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTrLedgersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tr_ledger', function (Blueprint $table) {
            $table->increments('id');
            $table->unique('ledg_id');
            $table->char('ledg_id', 36);
            $table->integer('ledge_fisyear');
            $table->string('ledg_number', 20);
            $table->date('ledg_date');
            $table->string('ledg_refno', 20);
            $table->decimal('ledg_debit', 14, 2);
            $table->decimal('ledg_credit', 14, 2);
            $table->string('ledg_description', 200);
            $table->char('coa_year', 4);
            $table->char('coa_code', 10);
            $table->integer('dept_id');
            $table->integer('created_by');
            $table->integer('updated_by');
            $table->integer('jour_type_id');
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
        Schema::dropIfExists('tr_ledger');
    }
}
