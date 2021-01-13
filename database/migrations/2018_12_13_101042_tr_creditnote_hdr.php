<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TrCreditnoteHdr extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tr_creditnote_hdr', function (Blueprint $table) {
            $table->increments('id');
            $table->date('creditnote_date');
            $table->string('creditnote_number', 20);
            $table->text('creditnote_keterangan')->nullable();
            $table->boolean('creditnote_post')->default(0);
            $table->datetime('posting_at')->nullable();
            $table->integer('posting_by')->nullable();
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
        Schema::dropIfExists('tr_creditnote_hdr');
    }
}
