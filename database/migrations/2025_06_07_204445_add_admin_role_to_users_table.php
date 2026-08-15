<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAdminRoleToUsersTable extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // If role is an enum, update it
            \DB::statement("ALTER TABLE users MODIFY role ENUM('customer', 'clerk', 'manager', 'admin') DEFAULT 'customer'");
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            \DB::statement("ALTER TABLE users MODIFY role ENUM('customer', 'clerk', 'manager') DEFAULT 'customer'");
        });
    }
}