<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TrFittingOut extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tr_fitting_out', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('fit_id');
            $table->string('out_number', 20);
            $table->date('out_date');
            $table->decimal('out_amount',12,2);
            $table->text('out_keterangan')->nullable();
            $table->text('out_refno')->nullable();
            $table->boolean('out_post')->default(0);
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
        Schema::dropIfExists('tr_fitting_out');
    }
}
