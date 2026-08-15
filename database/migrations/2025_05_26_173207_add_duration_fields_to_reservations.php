<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDurationFieldsToReservations extends Migration
{
    public function up()
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->string('duration_type')->nullable()->after('check_out_date'); // weeks or months
            $table->integer('duration_value')->nullable()->after('duration_type'); // number of weeks or months
        });
    }

    public function down()
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropColumn(['duration_type', 'duration_value']);
        });
    }
}