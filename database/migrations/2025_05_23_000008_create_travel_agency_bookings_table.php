<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTravelAgencyBookingsTable extends Migration
{
    public function up()
    {
        Schema::create('travel_agency_bookings', function (Blueprint $table) {
            $table->id(); // BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
            $table->unsignedBigInteger('travel_agency_id'); // BIGINT UNSIGNED NOT NULL
            $table->unsignedBigInteger('reservation_id'); // BIGINT UNSIGNED NOT NULL
            $table->decimal('discount_percentage', 5, 2)->default(0.00); // DECIMAL(5, 2) DEFAULT 0.00
            $table->decimal('quotation_amount', 10, 2); // DECIMAL(10, 2) NOT NULL
            $table->timestamps(); // created_at, updated_at
            $table->foreign('travel_agency_id')->references('id')->on('travel_agencies')->onDelete('cascade'); // FOREIGN KEY
            $table->foreign('reservation_id')->references('id')->on('reservations')->onDelete('cascade'); // FOREIGN KEY
        });
    }

    public function down()
    {
        Schema::dropIfExists('travel_agency_bookings');
    }
}