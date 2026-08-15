<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('room_types', function (Blueprint $table) {
            $table->decimal('price_per_night', 8, 2)->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('room_types', function (Blueprint $table) {
            $table->decimal('price_per_night', 8, 2)->nullable(false)->change();
        });
    }
};