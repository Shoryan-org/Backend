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
      Schema::create('blood_request',function(Blueprint $table){
        $table->id();
          $table->enum('blood_type',['A+','A-','B+','B-','AB+','AB-','O+','O-']);
           $table->enum('status',['PENDING','CANCELLED','FULLFILLED'])->default('PENDING');
            $table->enum('urgency',['EMERGENCY','URGENT','PLANNED']);
             $table->integer('no_of_units');
              $table->foreignId('address_id')->constrained();
              $table->string('notes');
               $table->foreignId('user_id')->constrained();
                $table->foreignId('hospital_id')->constrained();
                $table->timestamps();
               
      });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::dropIfExists('blood_request');
        
    }
};