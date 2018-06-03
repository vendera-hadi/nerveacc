<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddShowDetailAndCostAdminTypeCostDetails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ms_cost_detail', function (Blueprint $table) {
            $table->enum("costd_admin_type",["value","percent"])->default("value")->nullable();
            $table->boolean("costd_show_detail")->default(false)->nullable();
            $table->boolean("year_cycle")->default(true)->nullable();
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
            $table->dropColumn("costd_admin_type");
            $table->dropColumn("costd_show_detail");
        });
    }
}
