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
        // Step 1: Safely expand users.role (from enum to new enum with province specifics)
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 50)->change(); // Temporarily to string to avoid strict enum issues
        });

        // Step 2: Set back to enum with ALL valid roles (including new provinces)
        DB::statement("ALTER TABLE users MODIFY role ENUM(
            'admin', 'user', 'malsu', 'case_management', 'records',
            'province_albay', 'province_camarines_sur', 'province_camarines_norte',
            'province_catanduanes', 'province_masbate', 'province_sorsogon'
        ) NOT NULL DEFAULT 'user'");

        // Step 3: Also expand document_tracking.current_role (same list)
        Schema::table('document_tracking', function (Blueprint $table) {
            $table->string('current_role', 50)->change(); // Temp to string
        });

        DB::statement("ALTER TABLE document_tracking MODIFY current_role ENUM(
            'admin', 'malsu', 'case_management', 'records',
            'province_albay', 'province_camarines_sur', 'province_camarines_norte',
            'province_catanduanes', 'province_masbate', 'province_sorsogon'
        ) NOT NULL DEFAULT 'admin'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert users.role back to old limited enum
        DB::statement("ALTER TABLE users MODIFY role ENUM(
            'admin', 'malsu', 'case_management', 'province', 'records'
        ) NOT NULL DEFAULT 'user'");

        // Revert document_tracking.current_role
        DB::statement("ALTER TABLE document_tracking MODIFY current_role ENUM(
            'admin', 'malsu', 'case_management', 'province', 'records'
        ) NOT NULL DEFAULT 'admin'");
    }
};