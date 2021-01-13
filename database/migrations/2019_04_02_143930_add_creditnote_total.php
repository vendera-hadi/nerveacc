<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCreditnoteTotal extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tr_creditnote_hdr', function (Blueprint $table) {
            $table->integer('unit_id')->nullable();
            $table->integer('inv_id')->nullable();
            $table->decimal('total_amt', 10, 2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tr_creditnote_hdr', function (Blueprint $table) {
            $table->dropColumn('unit_id');
            $table->dropColumn('inv_id');
            $table->dropColumn('total_amt');
        });
    }
}
