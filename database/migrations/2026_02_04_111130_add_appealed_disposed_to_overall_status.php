<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE cases MODIFY COLUMN overall_status ENUM('Active', 'Completed', 'Dismissed', 'Appealed', 'Disposed') NOT NULL DEFAULT 'Active'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE cases MODIFY COLUMN overall_status ENUM('Active', 'Completed', 'Dismissed') NOT NULL DEFAULT 'Active'");
    }
};