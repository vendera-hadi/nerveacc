<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTrApInvoiceHdr extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tr_ap_invoice_hdr', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('spl_id');
            $table->date('invoice_date');
            $table->date('invoice_duedate');
            $table->string('invoice_no')->unique();
            $table->boolean('isdp')->default(false);
            $table->decimal('total', 12, 2)->default(0);
            $table->decimal('adjust', 10, 2)->default(0);
            $table->decimal('payment', 12, 2)->default(0);
            $table->decimal('ppn', 5, 2)->default(0);
            $table->boolean('posting')->default(false);
            $table->string('note')->nullable();
            $table->string('po_no')->nullable();
            $table->date('apdate')->nullable();
            $table->date('posting_at')->nullable();
            $table->integer('created_by');
            $table->integer('updated_by');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tr_ap_invoice_hdr');
    }
}
