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
        Schema::create('donations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('donor_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('blood_request_id')
                ->constrained('blood_requests')
                ->cascadeOnDelete();

            $table->unsignedInteger('no_of_units_donated');

            $table->timestamps();

            $table->unique(['donor_id', 'blood_request_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('donations');
    }
};
