<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class EmailQueues extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('email_queues', function (Blueprint $table) {
            $table->increments('id');
            $table->string('mailclass')->nullable();
            $table->string('to')->nullable();
            $table->string('cc')->nullable();
            $table->string('ref_id')->nullable();
            $table->string('status')->nullable();
            $table->datetime('sent_at')->nullable();
            $table->text('note')->nullable();
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
        Schema::dropIfExists('email_queues');
    }
}
