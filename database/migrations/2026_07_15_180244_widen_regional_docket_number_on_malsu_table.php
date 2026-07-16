<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE malsu MODIFY regional_docket_number TEXT NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE malsu MODIFY regional_docket_number VARCHAR(255) NULL');
    }
};