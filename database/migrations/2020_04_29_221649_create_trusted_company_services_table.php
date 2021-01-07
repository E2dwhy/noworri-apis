<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTrustedCompanyServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trusted_company_services', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('service');
            $table->string('company_id');
            $table->timestamps();
            
            $table->foreign('company_id')->references('id')->on('trusted_companies')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('trusted_company_services');
    }
}
