<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('initiator_id');
            $table->string('initiator_role');
            $table->string('destinator_id');
            $table->string('transaction_type');
            $table->string('delivery_phone');
            $table->string('name');
            $table->string('price');
            
            $table->longText('items')->nullable();
            $table->string('transaction_source')->default('vendor');
            
            $table->string('currency');
            $table->string('release_code');
            $table->integer('release_wrong_code')->default(0);
            $table->integer('deadDays')->default(0);
            $table->integer('deadHours')->default(0);
            $table->dateTime('deadline')->nullable();
            $table->dateTime('start')->nullable();
            $table->integer('revision');
            $table->string('transaction_key')->unique();
            $table->longText('requirement');
            $table->integer('etat')->default(1);
            $table->integer('deleted')->default(0);
            $table->string('payment_id')->nullable();
            
            $table->boolean('isUnlocked')->default(0);
            $table->dateTime('unlockDate')->nullable();
            $table->string('unlockConfirmState')->nullable();
            
            $table->string('qty_of_crypto')->nullable();
            $table->string('rate')->nullable();
            $table->string('buyer_wallet')->nullable();
            $table->string('proof_of_payment')->nullable();

            $table->timestamps();
            
            $table->foreign('initiator_id')->references('user_uid')->on('users')->onDelete('cascade');
            $table->foreign('destinator_id')->references('user_uid')->on('users')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transactions');
    }
}
