<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMsCostItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ms_cost_item', function (Blueprint $table) {
            $table->increments('id');
            $table->char('cost_code',10)->unique();
            $table->string('cost_name',50);
            $table->char('cost_coa_code',10)->nullable();
            $table->boolean('cost_isactive')->default(0);
            $table->integer('created_by');
            $table->integer('updated_by');
            $table->boolean('is_service_charge')->default(0);
            $table->boolean('is_insurance')->default(0);
            $table->boolean('is_sinking_fund')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ms_cost_item');
    }
}
