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
        Schema::create('hearing_process', function (Blueprint $table) {
            $table->id();
            $table->foreignId('case_id')->constrained('cases')->onDelete('cascade');
            $table->date('date_1st_mc_actual')->nullable(); 
            $table->date('first_mc_pct', 8, 2)->nullable(); 
            $table->string('status_1st_mc')->nullable(); 
            $table->date('date_2nd_last_mc')->nullable(); 
            $table->decimal('second_last_mc_pct', 8, 2)->nullable(); 
            $table->string('status_2nd_mc')->nullable(); 
            $table->string('case_folder_forwarded_to_ro')->nullable(); 
            $table->enum('complete_case_folder', ['Y', 'N'])->nullable(); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hearing_process');
    }
};