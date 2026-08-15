<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReportsTable extends Migration
{
    public function up()
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id(); // BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
            $table->unsignedBigInteger('branch_id'); // BIGINT UNSIGNED NOT NULL
            $table->date('report_date'); // DATE NOT NULL
            $table->integer('total_occupancy'); // INT NOT NULL
            $table->decimal('total_revenue', 10, 2); // DECIMAL(10, 2) NOT NULL
            $table->integer('no_show_count')->default(0); // INT DEFAULT 0
            $table->timestamps(); // created_at, updated_at
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade'); // FOREIGN KEY
        });
    }

    public function down()
    {
        Schema::dropIfExists('reports');
    }
}