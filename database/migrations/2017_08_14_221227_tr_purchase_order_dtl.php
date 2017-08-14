<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TrPurchaseOrderDtl extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tr_purchase_order_dtl', function (Blueprint $table) {
            $table->increments('id');
            $table->bigInteger('po_id');
            $table->string('note');
            $table->integer('qty');
            $table->decimal('amount', 10, 2);
            $table->decimal('ppn_amount', 10, 2);
            $table->boolean('is_ppn')->default(false);
            $table->char('coa_code',10);
            $table->char('ppn_coa_code',10)->nullable();
            $table->integer('dept_id');
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
        Schema::dropIfExists('tr_purchase_order_dtl');
    }
}
