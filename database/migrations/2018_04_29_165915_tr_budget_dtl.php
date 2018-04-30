<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TrBudgetDtl extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tr_budget_dtl', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('budget_id');
            $table->char('coa_code',10);
            $table->decimal('jan', 12, 2)->default(0);
            $table->decimal('feb', 12, 2)->default(0);
            $table->decimal('mar', 12, 2)->default(0);
            $table->decimal('apr', 12, 2)->default(0);
            $table->decimal('may', 12, 2)->default(0);
            $table->decimal('jun', 12, 2)->default(0);
            $table->decimal('jul', 12, 2)->default(0);
            $table->decimal('aug', 12, 2)->default(0);
            $table->decimal('sep', 12, 2)->default(0);
            $table->decimal('okt', 12, 2)->default(0);
            $table->decimal('nov', 12, 2)->default(0);
            $table->decimal('des', 12, 2)->default(0);
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
        Schema::dropIfExists('tr_budget_dtl');
    }
}
