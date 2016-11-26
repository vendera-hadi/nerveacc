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
            $table->char('cost_id',36);
            $table->char('cost_code',5)->unique();
            $table->string('cost_name',50);
            $table->boolean('cost_isactive')->default(0);
            $table->integer('created_by');
            $table->integer('updated_by');
            $table->integer('is_service_charge');
            $table->integer('is_insurance');
            $table->integer('is_sinking_fund');
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
