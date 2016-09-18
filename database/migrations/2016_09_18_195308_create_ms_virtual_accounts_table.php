<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMsVirtualAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ms_virtual_account', function (Blueprint $table) {
            $table->increments('id');
            $table->unique('viracc_id');
            $table->char('viracc_id', 36);
            $table->string('viracc_no', 20);
            $table->string('viracc_name', 35);
            $table->boolean('viracc_isactive')->default(0);
            $table->string('created_by', 15);
            $table->string('updated_by', 15);
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
        Schema::dropIfExists('ms_virtual_account');
    }
}
