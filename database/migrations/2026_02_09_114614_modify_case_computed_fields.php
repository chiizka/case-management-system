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
            // Add lapse_20_day_period if it doesn't exist
            if (!Schema::hasColumn('cases', 'lapse_20_day_period')) {
                $table->date('lapse_20_day_period')->nullable()->after('date_of_nr');
            }
            
            // Change pct_for_docketing from string to date
            if (Schema::hasColumn('cases', 'pct_for_docketing')) {
                $table->date('pct_for_docketing')->nullable()->change();
            }
            
            // Change first_mc_pct from string to integer
            if (Schema::hasColumn('cases', 'first_mc_pct')) {
                $table->integer('first_mc_pct')->nullable()->change();
            }
            
            // Change second_last_mc_pct from string to integer
            if (Schema::hasColumn('cases', 'second_last_mc_pct')) {
                $table->integer('second_last_mc_pct')->nullable()->change();
            }
            
            // Change po_pct from string to date
            if (Schema::hasColumn('cases', 'po_pct')) {
                $table->date('po_pct')->nullable()->change();
            }
            
            // Change case_folder_forwarded_to_ro to date if it's not already
            if (Schema::hasColumn('cases', 'case_folder_forwarded_to_ro')) {
                $table->date('case_folder_forwarded_to_ro')->nullable()->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cases', function (Blueprint $table) {
            // Revert changes back to string
            if (Schema::hasColumn('cases', 'pct_for_docketing')) {
                $table->string('pct_for_docketing', 255)->nullable()->change();
            }
            
            if (Schema::hasColumn('cases', 'first_mc_pct')) {
                $table->string('first_mc_pct', 255)->nullable()->change();
            }
            
            if (Schema::hasColumn('cases', 'second_last_mc_pct')) {
                $table->string('second_last_mc_pct', 255)->nullable()->change();
            }
            
            if (Schema::hasColumn('cases', 'po_pct')) {
                $table->string('po_pct', 255)->nullable()->change();
            }
            
            if (Schema::hasColumn('cases', 'case_folder_forwarded_to_ro')) {
                $table->string('case_folder_forwarded_to_ro', 255)->nullable()->change();
            }
            
            // Drop lapse_20_day_period if we added it
            if (Schema::hasColumn('cases', 'lapse_20_day_period')) {
                $table->dropColumn('lapse_20_day_period');
            }
        });
    }
};