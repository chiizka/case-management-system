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
        Schema::create('cases', function (Blueprint $table) {
            $table->id();
            $table->string('inspection_id');
            $table->string('case_no')->nullable();
            $table->string('establishment_name');
            $table->enum('current_stage', [
                '1: Inspections',
                '2: Docketing', 
                '3: Hearing',
                '4: Stage4Name',  // Replace with actual stage 4 name
                '5: Stage5Name',  // Replace with actual stage 5 name
                '6: Stage6Name',  // Replace with actual stage 6 name
                '7: Stage7Name'   // Replace with actual stage 7 name
            ]);
            $table->enum('overall_status', ['Active', 'Completed', 'Dismissed']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cases');
    }
};