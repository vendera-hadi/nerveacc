<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMsInvoiceTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ms_invoice_type', function (Blueprint $table) {
            $table->increments('id');
            $table->unique('invtp_code');
            $table->char('invtp_code',5);
            $table->string('invtp_name',50);
            $table->char('invtp_prefix',3);
            $table->string('created_by',15);
            $table->string('updated_by',15);
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
        Schema::dropIfExists('ms_invoice_type');
    }
}
