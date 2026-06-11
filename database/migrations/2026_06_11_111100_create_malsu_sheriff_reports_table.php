<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('malsu_sheriff_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('malsu_id')->constrained('malsu')->cascadeOnDelete();

            $table->date('report_month')->nullable();           // e.g. 2026-06-01 = June 2026
            $table->date('report_date_submitted')->nullable();  // actual submission date
            $table->text('report_content')->nullable();
            $table->string('scanned_file_path')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('malsu_sheriff_reports');
    }
};