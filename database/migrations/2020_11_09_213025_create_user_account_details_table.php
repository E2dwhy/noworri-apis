<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserAccountDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_account_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('user_id');
            $table->string('bank_name');
            $table->string('bank_code');
            $table->string('holder_name');
            $table->string('account_no');
            $table->string('recipient_code')->nullable();
            $table->string('type');
            $table->timestamps();
            
            $table->foreign('user_id')->references('user_uid')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_account_details');
    }
}
