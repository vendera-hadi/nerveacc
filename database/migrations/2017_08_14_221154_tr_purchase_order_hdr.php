<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TrPurchaseOrderHdr extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tr_purchase_order_hdr', function (Blueprint $table) {
            $table->increments('id');
            $table->string('po_number')->unique();
            $table->date('po_date');
            $table->integer('spl_id');
            $table->date('due_date');
            $table->string('terms')->nullable();
            $table->string('note');
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
        Schema::dropIfExists('tr_purchase_order_hdr');
    }
}
