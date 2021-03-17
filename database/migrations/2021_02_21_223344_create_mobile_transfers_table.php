<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMobileTransfersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mobile_transfers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('phone_no');
            $table->string('amount');
            $table->string('currency');
            $table->string('start_balance')->default('-1');
            $table->string('end_balance')->default('-1');
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
        Schema::dropIfExists('mobile_transfers');
    }
}
