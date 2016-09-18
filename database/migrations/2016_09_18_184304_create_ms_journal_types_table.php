<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMsJournalTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ms_journal_type', function (Blueprint $table) {
            $table->increments('id');
            $table->unique('jour_type_id');
            $table->char('jour_type_id',36);
            $table->char('jour_type_prefix',3);
            $table->boolean('jour_type_isactive')->default(0);
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
        Schema::dropIfExists('ms_journal_type');
    }
}
