<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTravelAgenciesTable extends Migration
{
    public function up()
    {
        Schema::create('travel_agencies', function (Blueprint $table) {
            $table->id(); // BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
            $table->string('name'); // VARCHAR(255) NOT NULL
            $table->string('contact_email'); // VARCHAR(255) NOT NULL
            $table->string('contact_number', 20)->nullable(); // VARCHAR(20)
            $table->boolean('is_verified')->default(false); // BOOLEAN DEFAULT FALSE
            $table->timestamps(); // created_at, updated_at
        });
    }

    public function down()
    {
        Schema::dropIfExists('travel_agencies');
    }
}