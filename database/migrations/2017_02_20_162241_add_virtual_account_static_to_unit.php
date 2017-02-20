<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddVirtualAccountStaticToUnit extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ms_unit', function (Blueprint $table) {
            $table->string('virtual_account')->nullable();
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
            $table->dropColumn('virtual_account'); 
        });
    }
}
