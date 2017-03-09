<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLabelFooterInvoice extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tr_invoice', function (Blueprint $table) {
            $table->text('label')->nullable();
            $table->text('footer')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tr_invoice', function (Blueprint $table) {
            $table->dropColumn('label');
            $table->dropColumn('footer');
        });
    }
}
