<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMsContractStatusesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ms_contract_status', function (Blueprint $table) {
            $table->increments('id');
            $table->unique('const_code');
            $table->char('const_code',5);
            $table->string('const_name',30);
            $table->tinyInteger('const_order');
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
        Schema::dropIfExists('ms_contract_status');
    }
}
