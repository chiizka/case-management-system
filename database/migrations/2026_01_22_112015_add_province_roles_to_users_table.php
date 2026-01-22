<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, update existing 'province' users to a specific province
        // You can adjust this based on your needs
        DB::table('users')
            ->where('role', 'province')
            ->update(['role' => 'province_albay']); // Default existing province users to Albay
        
        // Note: The role column already exists, we're just adding new enum values
        // Laravel will handle this through validation in the model and controller
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert all province roles back to generic 'province'
        DB::table('users')
            ->whereIn('role', [
                'province_albay',
                'province_camarines_sur',
                'province_camarines_norte',
                'province_catanduanes',
                'province_masbate',
                'province_sorsogon'
            ])
            ->update(['role' => 'province']);
    }
};