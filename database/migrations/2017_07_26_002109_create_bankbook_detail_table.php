<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBankbookDetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bankbook_detail', function (Blueprint $table) {
            $table->increments('id');
            $table->bigInteger('header_id');
            $table->char('coa_code',10);
            $table->decimal('debit', 14, 2);
            $table->decimal('credit', 14, 2);
            $table->string('description', 200);
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
        Schema::dropIfExists('bankbook_detail');
    }
}
