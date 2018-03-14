<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddValueTypeToCostDetail extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ms_cost_detail', function (Blueprint $table) {
            $table->enum('value_type', ['percent','value'])->default('value');
            $table->integer('percentage')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ms_cost_detail', function (Blueprint $table) {
            $table->dropColumn('value_type');
            $table->dropColumn('percentage');
        });
    }
}
