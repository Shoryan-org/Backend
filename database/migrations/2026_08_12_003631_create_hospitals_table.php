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
         Schema::create('Hospitals',function(Blueprint $table){
        $table->id();
          $table->string('name');
           $table->Decimal('latitude');
            $table->Decimal('longitude');
             $table->String('address_text');
              $table->foreignId('address_id')->constrained();
              $table->string('notes');
                $table->timestamps();
               
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hospitals');
    }
};
