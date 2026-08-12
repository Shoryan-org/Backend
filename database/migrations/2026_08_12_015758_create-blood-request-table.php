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
    Schema::create('blood_requests', function (Blueprint $table) {
      $table->id();
      $table->foreignId('requester_id')->constrained('users')->cascadeOnDelete();
      $table->foreignId('hospital_id')->constrained();

      $table->enum('blood_type', ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-']);
      $table->enum('status', ['PENDING', 'CANCELLED', 'FULFILLED'])->default('PENDING');
      $table->enum('urgency', ['EMERGENCY', 'URGENT', 'PLANNED']);
      $table->unsignedInteger('no_of_units');
      $table->text('notes')->nullable();

      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    //
    Schema::dropIfExists('blood_requests');
  }
};
