<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddProrateAkrual extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('akrual_inv', function (Blueprint $table) {
            $table->decimal('prorate_amount', 12, 2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('akrual_inv', function (Blueprint $table) {
            $table->dropColumn('prorate_amount');
        });
    }
}
