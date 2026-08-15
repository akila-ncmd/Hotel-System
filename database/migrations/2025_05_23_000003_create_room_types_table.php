<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRoomTypesTable extends Migration
{
    public function up()
    {
        Schema::create('room_types', function (Blueprint $table) {
            $table->id(); // BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
            $table->string('name'); // VARCHAR(255) NOT NULL
            $table->text('description')->nullable(); // TEXT
            $table->decimal('price_per_night', 10, 2); // DECIMAL(10, 2) NOT NULL
            $table->decimal('weekly_rate', 10, 2)->nullable(); // DECIMAL(10, 2) NULL
            $table->decimal('monthly_rate', 10, 2)->nullable(); // DECIMAL(10, 2) NULL
            $table->integer('max_occupants'); // INT NOT NULL
            $table->boolean('is_suite')->default(false); // BOOLEAN DEFAULT FALSE
            $table->timestamps(); // created_at, updated_at
        });
    }

    public function down()
    {
        Schema::dropIfExists('room_types');
    }
}
