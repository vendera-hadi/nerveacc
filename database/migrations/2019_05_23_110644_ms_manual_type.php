<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MsManualType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ms_manual_type', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name',100);
            $table->string('name_detail',100);
            $table->char('coa_code', 10);
            $table->decimal('amount',12,2);
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
        Schema::dropIfExists('ms_manual_type');
    }
}
