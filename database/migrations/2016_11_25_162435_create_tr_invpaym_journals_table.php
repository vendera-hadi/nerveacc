<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTrInvpaymJournalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tr_invpaym_journal', function (Blueprint $table) {
            $table->increments('id');
            $table->date('ipayjour_date');
            $table->string('ipayjour_voucher',15);
            $table->string('ipayjour_note',100);
            $table->char('coa_code',10);
            $table->decimal('ipayjour_debit',12,2);
            $table->decimal('ipayjour_credit',12,2);
            $table->integer('invpayh_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tr_invpaym_journal');
    }
}
