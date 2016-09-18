<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMsSuppliersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ms_supplier', function (Blueprint $table) {
            $table->increments('id');
            $table->unique('spl_id');
            $table->unique('spl_code');
            $table->char('spl_id', 36);
            $table->char('spl_code', 5);
            $table->string('spl_name', 150);
            $table->string('spl_address', 200);
            $table->string('spl_city', 255);
            $table->char('spl_postal_code', 5);
            $table->string('spl_phone', 20);
            $table->string('spl_fax', 20);
            $table->string('spl_cperson', 35);
            $table->char('spl_npwp', 15);
            $table->boolean('spl_isactive')->default(0);
            $table->string('created_by', 15);
            $table->string('updated_by', 15);
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
        Schema::dropIfExists('ms_supplier');
    }
}
