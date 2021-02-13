<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('first_name');
            $table->string('user_uid')->unique();
            $table->string('user_name')->unique();
            $table->string('mobile_phone')->unique();
            $table->boolean('buyer')->nullable();
            $table->boolean('seller')->nullable();
            $table->tinyInteger('type')->default('0');
            $table->tinyInteger('account')->default('0');
            $table->string('country_code')->nullable();
            $table->string('dailing_code')->nullable();
            $table->string('currency');
            $table->string('email')->unique();
            $table->string('photo')->nullable();
            $table->string('otp')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->text('web_token');
            $table->text('fcm_token');
            $table->tinyInteger('status')->default('0');
            $table->rememberToken();
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
        Schema::dropIfExists('users');
    }
}
