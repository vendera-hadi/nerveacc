<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAkrualInvStatus extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('akrual_inv', function (Blueprint $table) {
            $table->string('last_status', 100)->nullable();
            $table->date('last_process', 100)->nullable();
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
            $table->dropColumn('last_status');
            $table->dropColumn('last_process');
        });
    }
}
