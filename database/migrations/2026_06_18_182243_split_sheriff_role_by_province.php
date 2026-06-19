<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Converts the single 'sheriff_designate' role (with province tracked in
     * a separate 'province' column) into six province-qualified roles —
     * sheriff_albay, sheriff_camarines_sur, sheriff_camarines_norte,
     * sheriff_catanduanes, sheriff_masbate, sheriff_sorsogon — matching the
     * existing province_* role pattern so document tracking's
     * `current_role = user->role` comparison keeps working unchanged.
     */
    public function up(): void
    {
        // Step 1: Widen the enum to include both old and new values. This
        // lets us backfill existing rows before removing 'sheriff_designate'
        // — MySQL will error/truncate if we narrow the enum while rows still
        // hold a value that's being dropped.
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
                'sheriff_designate',
                'sheriff_albay',
                'sheriff_camarines_sur',
                'sheriff_camarines_norte',
                'sheriff_catanduanes',
                'sheriff_masbate',
                'sheriff_sorsogon'
            ) NOT NULL DEFAULT 'user'
        ");

        // Step 2: Backfill existing sheriff_designate users into the matching
        // province-qualified role, based on their current `province` column.
        $provinceMap = [
            'albay' => 'sheriff_albay',
            'camarines_sur' => 'sheriff_camarines_sur',
            'camarines_norte' => 'sheriff_camarines_norte',
            'catanduanes' => 'sheriff_catanduanes',
            'masbate' => 'sheriff_masbate',
            'sorsogon' => 'sheriff_sorsogon',
        ];

        foreach ($provinceMap as $province => $newRole) {
            DB::table('users')
                ->where('role', 'sheriff_designate')
                ->where('province', $province)
                ->update(['role' => $newRole]);
        }

        // Step 3: Refuse to proceed if any sheriff_designate rows couldn't be
        // mapped (no province set, or an unexpected province value). Better
        // to stop the migration than silently leave/misassign a sheriff.
        $unmapped = DB::table('users')->where('role', 'sheriff_designate')->count();
        if ($unmapped > 0) {
            throw new \RuntimeException(
                "{$unmapped} user(s) still have role 'sheriff_designate' with no matching " .
                "province value. Assign a valid province to these users, then re-run this migration."
            );
        }

        // Step 4: Now safe to drop 'sheriff_designate' from the enum.
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
                'sheriff_albay',
                'sheriff_camarines_sur',
                'sheriff_camarines_norte',
                'sheriff_catanduanes',
                'sheriff_masbate',
                'sheriff_sorsogon'
            ) NOT NULL DEFAULT 'user'
        ");

        // Note: the `province` column itself is left in place (nullable,
        // simply unused by sheriff roles going forward). Dropping it is a
        // separate decision — see chat before doing that in another migration.
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Step 1: Widen enum again so we can map values back.
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
                'sheriff_designate',
                'sheriff_albay',
                'sheriff_camarines_sur',
                'sheriff_camarines_norte',
                'sheriff_catanduanes',
                'sheriff_masbate',
                'sheriff_sorsogon'
            ) NOT NULL DEFAULT 'user'
        ");

        // Step 2: Map each province-qualified sheriff role back to
        // sheriff_designate, restoring the province column value.
        $provinceMap = [
            'sheriff_albay' => 'albay',
            'sheriff_camarines_sur' => 'camarines_sur',
            'sheriff_camarines_norte' => 'camarines_norte',
            'sheriff_catanduanes' => 'catanduanes',
            'sheriff_masbate' => 'masbate',
            'sheriff_sorsogon' => 'sorsogon',
        ];

        foreach ($provinceMap as $oldRole => $province) {
            DB::table('users')
                ->where('role', $oldRole)
                ->update(['role' => 'sheriff_designate', 'province' => $province]);
        }

        // Step 3: Drop the six province-qualified sheriff roles from the enum.
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
    }
};