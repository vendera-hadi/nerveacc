<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMeterAirListrikToUnit extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ms_unit', function (Blueprint $table) {
            $table->string('meter_listrik')->nullable();
            $table->string('meter_air')->nullable();
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
            $table->dropColumn('meter_listrik');
            $table->dropColumn('meter_air');
        });
    }
}
