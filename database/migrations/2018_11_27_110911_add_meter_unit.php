<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMeterUnit extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ms_unit', function (Blueprint $table) {
            $table->decimal('air_start', 10, 2)->default(0);
            $table->decimal('listrik_start', 10, 2)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ms_unit', function (Blueprint $table) {
            $table->dropColumn('air_start');
            $table->dropColumn('listrik_start');
        });
    }
}
