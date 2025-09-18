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
        Schema::table('inspections', function (Blueprint $table) {
            // Drop the existing computed column that calculates from date_of_inspection
            $table->dropColumn('lapse_20_day_period');
        });

        Schema::table('inspections', function (Blueprint $table) {
            // Add the corrected computed column that calculates from date_of_nr
            $table->date('lapse_20_day_period')->nullable()->storedAs('DATE_ADD(date_of_nr, INTERVAL 20 DAY)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inspections', function (Blueprint $table) {
            // Drop the corrected computed column
            $table->dropColumn('lapse_20_day_period');
        });

        Schema::table('inspections', function (Blueprint $table) {
            // Restore the original computed column (calculates from date_of_inspection)
            $table->date('lapse_20_day_period')->nullable()->storedAs('DATE_ADD(date_of_inspection, INTERVAL 20 DAY)');
        });
    }
};