<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMsMarketingAgentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ms_marketing_agent', function (Blueprint $table) {
            $table->increments('id');
            $table->unique('mark_id');
            $table->char('mark_id', 36);
            $table->string('mark_code', 5);
            $table->string('mark_name', 50);
            $table->string('mark_phone', 20);
            $table->boolean('mark_isactive')->default(0);
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
        Schema::dropIfExists('ms_marketing_agent');
    }
}
