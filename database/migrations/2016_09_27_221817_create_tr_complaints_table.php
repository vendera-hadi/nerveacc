<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTrComplaintsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tr_complaint', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('comtr_no',15)->unique();
            $table->dateTime('comtr_date');
            $table->string('comtr_note',50)->nullable();
            $table->dateTime('comtr_handling_date')->nullable();
            $table->string('comtr_handling_by',25)->nullable();
            $table->dateTime('comtr_finish_date')->nullable();
            $table->text('comtr_handling_note')->nullable();
            $table->bigInteger('compl_id');
            $table->bigInteger('unit_id');
            $table->string('created_by');
            $table->string('updated_by');
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
        Schema::dropIfExists('tr_complaint');
    }
}
