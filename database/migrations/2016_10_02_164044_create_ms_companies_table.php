<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMsCompaniesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ms_company', function (Blueprint $table) {
            $table->increments('id');
            $table->string('comp_name',100);
            $table->string('comp_address',150);
            $table->string('comp_phone',20);
            $table->string('comp_fax',20);
            $table->string('comp_sign_inv_name',40);
            $table->decimal('comp_build_insurance', 18, 2);
            $table->decimal('comp_npp_insurance', 12, 8);
            $table->char('cashbk_id',36);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ms_company');
    }
}
