<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddArCoaCodeToCostItem extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ms_cost_item', function (Blueprint $table) {
            $table->char('ar_coa_code',10)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ms_cost_item', function (Blueprint $table) {
            $table->dropColumn('ar_coa_code');
        });
    }
}
