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
        Schema::table('hearing_process', function (Blueprint $table) {
            // Change first_mc_pct from date to string
            $table->string('first_mc_pct')->nullable()->change();
            // Change second_last_mc_pct from decimal to string  
            $table->string('second_last_mc_pct')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hearing_process', function (Blueprint $table) {
            // Revert back to original types
            $table->date('first_mc_pct')->nullable()->change();
            $table->decimal('second_last_mc_pct', 8, 2)->nullable()->change();
        });
    }
};