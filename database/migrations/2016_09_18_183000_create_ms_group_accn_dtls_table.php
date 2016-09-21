<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMsGroupAccnDtlsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ms_group_accn_dtl', function (Blueprint $table) {
            $table->increments('id');
            $table->char('grpaccn_id',36);
            $table->char('coa_year',36);
            $table->char('coa_code',10);
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
        Schema::dropIfExists('ms_group_accn_dtl');
    }
}
