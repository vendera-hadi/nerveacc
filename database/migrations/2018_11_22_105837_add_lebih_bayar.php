<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLebihBayar extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tr_invoice', function (Blueprint $table) {
            $table->decimal('total_excess_payment', 12, 2)->default(0);
            $table->decimal('current_last_outstanding', 12, 2)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $table->dropColumn('total_excess_payment');
        $table->dropColumn('current_last_outstanding');
    }
}
