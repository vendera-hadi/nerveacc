<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMsTenantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ms_tenant', function (Blueprint $table) {
            $table->increments('id');
            $table->char('tenan_code', 15)->unique();
            $table->string('tenan_name', 80);
            $table->string('tenan_idno', 20);
            $table->string('tenan_phone', 20);
            $table->string('tenan_fax', 20)->nullable();
            $table->string('tenan_email', 80);
            $table->string('tenan_address', 150);
            $table->string('tenan_npwp', 15);
            $table->string('tenan_taxname', 50);
            $table->string('tenan_tax_address', 150);
            $table->boolean('tenan_isppn')->default(false);
            $table->boolean('tenan_ispkp')->default(false);
            $table->integer('tent_id');
            $table->string('created_by', 15);
            $table->string('updated_by', 15);
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
        Schema::dropIfExists('ms_tenant');
    }
}
