<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTrustedCompaniesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('trusted_companies', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('user_id');
            $table->string('businessname');
            $table->string('fullname');
            $table->string('profilpicture');
            $table->string('city');
            $table->string('country');
            $table->string('sector');
            $table->string('services');
            $table->string('address');
            $table->string('businessphone');
            $table->string('additionnalphone')->nullable();
            $table->string('facebook')->nullable();
            $table->string('instagram')->nullable();
            $table->string('whatsapp')->nullable();
            $table->string('identitycard');
            $table->string('identitycardfile');
            $table->string('identitycardverifyfile');
            
            $table->string('state')->default('pending');
            $table->timestamps();
            
            $table->foreign('user_id')->references('user_uid')->on('users')->onDelete('cascade');        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('trusted_companies');
    }
}
