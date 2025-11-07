<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Update ENUMs to include "records"
        DB::statement("ALTER TABLE document_tracking 
            MODIFY COLUMN current_role ENUM('admin', 'malsu', 'case_management', 'province', 'records') NOT NULL");

        DB::statement("ALTER TABLE document_tracking_history 
            MODIFY COLUMN from_role ENUM('admin', 'malsu', 'case_management', 'province', 'records') NULL");

        DB::statement("ALTER TABLE document_tracking_history 
            MODIFY COLUMN to_role ENUM('admin', 'malsu', 'case_management', 'province', 'records') NOT NULL");
    }

    public function down(): void
    {
        // Rollback to previous ENUM definition (without records)
        DB::statement("ALTER TABLE document_tracking 
            MODIFY COLUMN current_role ENUM('admin', 'malsu', 'case_management', 'province') NOT NULL");

        DB::statement("ALTER TABLE document_tracking_history 
            MODIFY COLUMN from_role ENUM('admin', 'malsu', 'case_management', 'province') NULL");

        DB::statement("ALTER TABLE document_tracking_history 
            MODIFY COLUMN to_role ENUM('admin', 'malsu', 'case_management', 'province') NOT NULL");
    }
};
