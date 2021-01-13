<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TrManualinvHdr extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tr_manualinv_hdr', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('unit_id');
            $table->integer('tenan_id');
            $table->string('manual_number', 30);
            $table->date('manual_date');
            $table->date('manual_duedate');
            $table->decimal('manual_amount',12,2);
            $table->integer('cashbk_id');
            $table->integer('manual_type');
            $table->text('manual_footer')->nullable();
            $table->boolean('manual_post')->default(0);
            $table->datetime('posting_at')->nullable();
            $table->integer('created_by');
            $table->integer('updated_by');
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
        Schema::dropIfExists('tr_manualinv_hdr');
    }
}
