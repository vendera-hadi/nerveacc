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
        Schema::create('tr_bank', function (Blueprint $table) {
            $table->increments('id');
            $table->string('trbank_no')->unique();
            $table->datetime('trbank_date');
            $table->char('trbank_group',3)->nullable();
            $table->decimal('trbank_in', 12, 2)->default(0);
            $table->decimal('trbank_out', 12, 2)->default(0);
            $table->datetime('trbank_girodate')->nullable();
            $table->string('trbank_girono', 10)->nullable();
            $table->decimal('trbank_charge', 8, 2)->default(0);
            $table->string('trbank_note')->nullable();
            $table->string('trbank_recipient');
            $table->char('coa_code',20);
            $table->boolean('trbank_rekon')->default(0);
            $table->boolean('trbank_post')->default(0);
            $table->datetime('posting_at')->nullable();
            $table->integer('paymtp_id');
            $table->integer('cashbk_id');
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tr_bank');
    }
}
