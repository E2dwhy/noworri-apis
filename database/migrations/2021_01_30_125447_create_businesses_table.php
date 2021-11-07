<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBusinessesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('businesses', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('user_id');
            $table->string('industry');
            $table->string('delivery_no');
            $table->string('business_address');
            $table->string('business_phone');
            $table->string('business_logo')->nullable();
            $table->string('business_email');
            $table->string('business_legal_name');
            $table->string('trading_name');
            $table->string('description');
            $table->string('company_document_path')->nullable();
            $table->boolean('legally_registered')->default(0);
            $table->string('category');
            $table->string('city');
            $table->string('country');
            $table->string('region');
            $table->string('owner_fname');
            $table->string('owner_lname');
            $table->string('owner_address');
            $table->string('DOB');
            $table->string('id_type');
            $table->string('id_card');
            $table->string('nationality');
            $table->string('status')->default('pending');
            $table->string('api_key_live')->nullable();
            $table->string('api_key_test')->nullable();
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
        Schema::dropIfExists('businesses');
    }
}
