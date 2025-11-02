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
        Schema::create('search_logs', function (Blueprint $table) {
            $table->id();
            $table->string('partial_case_number')->nullable();
            $table->string('criminal_case_number')->nullable();
            $table->string('name')->nullable();
            $table->string('mother_name')->nullable();
            $table->string('date_of_birth')->nullable();
            $table->string('job')->nullable();
            $table->string('address')->nullable();
            $table->string('charges')->nullable();
            $table->string('judgment_date')->nullable();
            $table->string('judgment_operative')->nullable();
            $table->integer('user_id');
            $table->longText("result")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('search_logs');
    }
};
