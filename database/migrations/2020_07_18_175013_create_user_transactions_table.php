<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_transactions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('user_id');
            $table->string('user_role');
            $table->string('user_name')->nullable();
            $table->string('user_phone')->nullable();
            $table->string('owner_id');
            $table->string('owner_role');
            $table->string('owner_name')->nullable();
            $table->string('owner_phone')->nullable();
            $table->string('transaction_type');
            $table->string('service');
            $table->float('price');
            $table->float('noworri_fees')->nullable();
            $table->float('total_price')->nullable();
            $table->integer('deadDays')->default(0);
            $table->integer('deadHours')->default(0);
            $table->dateTime('deadline')->nullable();
            $table->dateTime('start')->nullable();
            $table->string('deadline_type')->nullable();
            $table->integer('revision')->nullable();
            $table->string('transaction_key')->unique();
            $table->longText('requirement')->nullable();
            $table->string('file_path')->nullable();
            $table->integer('etat')->default(1);
            $table->integer('deleted')->default(0);
            $table->timestamps();
            
            $table->foreign('user_id')->references('user_uid')->on('users')->onDelete('cascade');
            $table->foreign('owner_id')->references('user_uid')->on('users')->onDelete('cascade');        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_transactions');
    }
}
