<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMsCategoryAssetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ms_category_asset', function (Blueprint $table) {
            $table->increments('id');
            $table->char('catas_id', 2)->unique();
            $table->string('catas_name', 40);
            $table->integer('catas_age');
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
        Schema::dropIfExists('ms_category_asset');
    }
}
