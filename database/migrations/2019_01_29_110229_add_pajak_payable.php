<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPajakPayable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tr_ap_payment_hdr', function (Blueprint $table) {
            $table->integer('pajak_id')->nullable();
            $table->decimal('pajak_amount', 12, 2)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $table->dropColumn('pajak_id');
        $table->dropColumn('pajak_amount');
    }
}
