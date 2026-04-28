<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone_number')->nullable();
            $table->string('full_name')->nullable();
            $table->integer('code')->nullable()->nullable();
            $table->integer('password_state')->nullable();
            $table->dateTime('adding_date')->nullable();
            $table->unsignedInteger('department_id')->nullable();
            $table->integer('inserted_user_id')->nullable();
            $table->unsignedInteger('user_type_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
};
