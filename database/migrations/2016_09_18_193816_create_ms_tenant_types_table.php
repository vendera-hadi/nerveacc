<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMsTenantTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ms_tenant_type', function (Blueprint $table) {
            $table->increments('id');
            $table->unique('tent_id');
            $table->char('tent_id', 36);
            $table->string('tent_name', 15);
            $table->boolean('tent_isowner')->default(0);
            $table->string('created_by',15);
            $table->string('updated_by',15);
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
        Schema::dropIfExists('ms_tenant_type');
    }
}
