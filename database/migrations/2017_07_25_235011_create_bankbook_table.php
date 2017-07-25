<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBankbookTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bankbook_header', function (Blueprint $table) {
            $table->increments('id');
            $table->string('voucher_no')->unique();
            $table->string('note')->nullable();
            $table->date('transaction_date');
            $table->integer('paymtp_id');
            $table->date('check_date')->nullable();
            $table->decimal('amount',12,2);
            $table->boolean('is_posted')->default(0);
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
        Schema::dropIfExists('bankbook_header');
    }
}
