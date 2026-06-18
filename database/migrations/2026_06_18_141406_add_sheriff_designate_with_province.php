<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * - Adds 'sheriff_designate' to the role enum
     * - Adds a nullable 'province' column for sheriff province assignment
     */
    public function up(): void
    {
        // Step 1: Add 'sheriff_designate' to the role enum (keep all existing values)
        DB::statement("
            ALTER TABLE users
            MODIFY COLUMN role ENUM(
                'admin',
                'user',
                'malsu',
                'case_management',
                'records',
                'province_albay',
                'province_camarines_sur',
                'province_camarines_norte',
                'province_catanduanes',
                'province_masbate',
                'province_sorsogon',
                'sheriff_designate'
            ) NOT NULL DEFAULT 'user'
        ");

        // Step 2: Add the nullable province column for sheriff assignments
        DB::statement("
            ALTER TABLE users
            ADD COLUMN province ENUM(
                'albay',
                'camarines_sur',
                'camarines_norte',
                'catanduanes',
                'masbate',
                'sorsogon'
            ) NULL DEFAULT NULL AFTER role
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop the province column
        DB::statement("ALTER TABLE users DROP COLUMN province");

        // Remove 'sheriff_designate' from role enum
        DB::table('users')
            ->where('role', 'sheriff_designate')
            ->update(['role' => 'user']);

        DB::statement("
            ALTER TABLE users
            MODIFY COLUMN role ENUM(
                'admin',
                'user',
                'malsu',
                'case_management',
                'records',
                'province_albay',
                'province_camarines_sur',
                'province_camarines_norte',
                'province_catanduanes',
                'province_masbate',
                'province_sorsogon'
            ) NOT NULL DEFAULT 'user'
        ");
    }
};