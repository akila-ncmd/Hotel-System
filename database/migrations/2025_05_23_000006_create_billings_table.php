<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBillingsTable extends Migration
{
    public function up()
    {
        Schema::create('billings', function (Blueprint $table) {
            $table->id(); // BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
            $table->unsignedBigInteger('reservation_id'); // BIGINT UNSIGNED NOT NULL
            $table->unsignedBigInteger('user_id'); // BIGINT UNSIGNED NOT NULL
            $table->unsignedBigInteger('branch_id'); // BIGINT UNSIGNED NOT NULL
            $table->decimal('total_amount', 10, 2); // DECIMAL(10, 2) NOT NULL
            $table->enum('payment_method', ['credit_card', 'cash', 'travel_agency'])->nullable(); // ENUM NULL
            $table->enum('payment_status', ['pending', 'paid', 'no_show'])->default('pending'); // ENUM DEFAULT 'pending'
            $table->decimal('restaurant_charges', 10, 2)->default(0.00); // DECIMAL(10, 2) DEFAULT 0.00
            $table->decimal('room_service_charges', 10, 2)->default(0.00); // DECIMAL(10, 2) DEFAULT 0.00
            $table->decimal('laundry_charges', 10, 2)->default(0.00); // DECIMAL(10, 2) DEFAULT 0.00
            $table->decimal('telephone_charges', 10, 2)->default(0.00); // DECIMAL(10, 2) DEFAULT 0.00
            $table->decimal('club_facility_charges', 10, 2)->default(0.00); // DECIMAL(10, 2) DEFAULT 0.00
            $table->timestamps(); // created_at, updated_at
            $table->foreign('reservation_id')->references('id')->on('reservations')->onDelete('cascade'); // FOREIGN KEY
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade'); // FOREIGN KEY
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade'); // FOREIGN KEY
        });
    }

    public function down()
    {
        Schema::dropIfExists('billings');
    }
}