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
            $table->string('comp_fax',20)->nullable();
            $table->string('comp_sign_inv_name',40);
            $table->decimal('comp_build_insurance', 18, 2);
            $table->decimal('comp_npp_insurance', 18, 2);
            $table->decimal('comp_materai1', 4, 0);
            $table->decimal('comp_materai1_amount', 10, 2)->default(0);
            $table->decimal('comp_materai2', 4, 0);
            $table->decimal('comp_materai2_amount', 10, 2)->default(0);
            $table->decimal('comp_sqrt', 18, 2);
            $table->integer('cashbk_id');
            $table->string('comp_image',255)->nullable();
            $table->string('comp_sign_position',255)->nullable();
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
