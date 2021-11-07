<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUniwalletsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('uniwallets', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('merchantId');
            $table->string('productId');
            $table->string('refNo');
            $table->string('msisdn');
            $table->string('amount');
            $table->string('network');
            $table->string('narration');
            $table->string('result')->default('0');
            //$table->string('email')->unique();
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
        Schema::dropIfExists('uniwallets');
    }
}
