<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ReminderHeader extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reminder_header', function (Blueprint $table) {
            $table->increments('id');
            $table->string('reminder_no', 20);
            $table->integer('unit_id');
            $table->date('reminder_date');
            $table->date('lastsent_date')->nullable();
            $table->integer('sent_counter')->nullable();
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
        Schema::dropIfExists('reminder_header');
    }
}
