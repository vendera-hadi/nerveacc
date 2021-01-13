<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddReminderContent extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reminder_header', function (Blueprint $table) {
            $table->text('isi_content')->nullable();
            $table->integer('sp_type')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('reminder_header', function (Blueprint $table) {
            $table->dropColumn('isi_content');
            $table->dropColumn('sp_type');
        });
    }
}
