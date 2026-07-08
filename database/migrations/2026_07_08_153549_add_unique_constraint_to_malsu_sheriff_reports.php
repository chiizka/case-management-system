<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Find (malsu_id, report_month) pairs with duplicates
        $duplicates = DB::table('malsu_sheriff_reports')
            ->select('malsu_id', 'report_month')
            ->whereNotNull('report_month')
            ->groupBy('malsu_id', 'report_month')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        // 2. For each pair, keep the most recently submitted row, delete the rest
        foreach ($duplicates as $dupe) {
            $rows = DB::table('malsu_sheriff_reports')
                ->where('malsu_id', $dupe->malsu_id)
                ->where('report_month', $dupe->report_month)
                ->orderByDesc('report_date_submitted')
                ->orderByDesc('id')
                ->pluck('id');

            $idsToDelete = $rows->slice(1);
            if ($idsToDelete->isNotEmpty()) {
                DB::table('malsu_sheriff_reports')->whereIn('id', $idsToDelete)->delete();
            }
        }

        // 3. Now safe to add the constraint
        Schema::table('malsu_sheriff_reports', function (Blueprint $table) {
            $table->unique(['malsu_id', 'report_month'], 'malsu_report_month_unique');
        });
    }

    public function down(): void
    {
        Schema::table('malsu_sheriff_reports', function (Blueprint $table) {
            $table->dropUnique('malsu_report_month_unique');
        });
    }
};