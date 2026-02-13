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
        Schema::table('cases', function (Blueprint $table) {
            // Change pct_96_days from string to date
            if (Schema::hasColumn('cases', 'pct_96_days')) {
                $table->date('pct_96_days')->nullable()->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cases', function (Blueprint $table) {
            // Revert pct_96_days back to string
            if (Schema::hasColumn('cases', 'pct_96_days')) {
                $table->string('pct_96_days', 255)->nullable()->change();
            }
        });
    }
};