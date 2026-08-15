<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id(); // BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
            $table->string('name'); // VARCHAR(255) NOT NULL
            $table->string('email')->unique(); // VARCHAR(255) UNIQUE NOT NULL
            $table->string('password'); // VARCHAR(255) NOT NULL
            $table->enum('role', ['customer', 'clerk', 'manager']); // ENUM('customer', 'clerk', 'manager') NOT NULL
            $table->unsignedBigInteger('branch_id')->nullable(); // BIGINT UNSIGNED NULL
            $table->string('nationality', 100)->nullable(); // VARCHAR(100) NULL
            $table->string('contact_number', 20)->nullable(); // VARCHAR(20) NULL
            $table->timestamps(); // created_at, updated_at
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('set null'); // FOREIGN KEY
        });
    }

    public function down()
    {
        Schema::dropIfExists('users');
    }
}