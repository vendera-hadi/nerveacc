<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMsCostDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ms_cost_detail', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('cost_id');
            $table->string('costd_name',100);
            $table->string('costd_unit',10)->nullable();
            $table->decimal('costd_rate', 10, 2);
            $table->decimal('costd_burden', 10, 2);
            $table->decimal('costd_admin', 10, 2);
            $table->boolean('costd_ismeter')->default(0);
            $table->integer('daya')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ms_cost_detail');
    }
}
