<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStepTransTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void  
     */
    public function up()
    {
        Schema::create('step_trans', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('transaction_id');
            $table->integer('step');
            $table->boolean('accepted');
            $table->string('description')->nullable();
            $table->timestamps();
            $table->foreign('transaction_id')->references('transaction_key')->on('transactions')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('step_trans');
    }
}
