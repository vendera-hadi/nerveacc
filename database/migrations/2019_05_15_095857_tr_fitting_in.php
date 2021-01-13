<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TrFittingIn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tr_fitting_in', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('unit_id');
            $table->integer('tenan_id');
            $table->string('fit_number', 20);
            $table->date('fit_date');
            $table->decimal('fit_amount',12,2);
            $table->text('fit_keterangan')->nullable();
            $table->text('fit_refno')->nullable();
            $table->boolean('fit_post')->default(0);
            $table->datetime('posting_at')->nullable();
            $table->integer('created_by');
            $table->integer('updated_by');
            $table->integer('flag_selesai')->default(0);
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
        Schema::dropIfExists('tr_fitting_in');
    }
}
