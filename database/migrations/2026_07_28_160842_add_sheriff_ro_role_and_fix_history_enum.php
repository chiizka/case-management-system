<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // users.role
        DB::statement("
            ALTER TABLE users
            MODIFY COLUMN role ENUM(
                'admin','user','malsu','case_management','records',
                'province_albay','province_camarines_sur','province_camarines_norte',
                'province_catanduanes','province_masbate','province_sorsogon',
                'sheriff_albay','sheriff_camarines_sur','sheriff_camarines_norte',
                'sheriff_catanduanes','sheriff_masbate','sheriff_sorsogon','sheriff_ro'
            ) NOT NULL DEFAULT 'user'
        ");

        // document_tracking.current_role
        DB::statement("
            ALTER TABLE document_tracking
            MODIFY COLUMN current_role ENUM(
                'admin','malsu','case_management','records',
                'province_albay','province_camarines_sur','province_camarines_norte',
                'province_catanduanes','province_masbate','province_sorsogon',
                'sheriff_albay','sheriff_camarines_sur','sheriff_camarines_norte',
                'sheriff_catanduanes','sheriff_masbate','sheriff_sorsogon','sheriff_ro'
            ) NOT NULL
        ");

        // document_tracking_history.from_role — FIX: add missing sheriff roles + ro
        DB::statement("
            ALTER TABLE document_tracking_history
            MODIFY COLUMN from_role ENUM(
                'admin','malsu','case_management','records',
                'province_albay','province_camarines_sur','province_camarines_norte',
                'province_catanduanes','province_masbate','province_sorsogon',
                'sheriff_albay','sheriff_camarines_sur','sheriff_camarines_norte',
                'sheriff_catanduanes','sheriff_masbate','sheriff_sorsogon','sheriff_ro'
            ) NULL
        ");

        // document_tracking_history.to_role — FIX: add missing sheriff roles + ro
        DB::statement("
            ALTER TABLE document_tracking_history
            MODIFY COLUMN to_role ENUM(
                'admin','malsu','case_management','records',
                'province_albay','province_camarines_sur','province_camarines_norte',
                'province_catanduanes','province_masbate','province_sorsogon',
                'sheriff_albay','sheriff_camarines_sur','sheriff_camarines_norte',
                'sheriff_catanduanes','sheriff_masbate','sheriff_sorsogon','sheriff_ro'
            ) NOT NULL
        ");
    }

    public function down(): void
    {
        // Revert document_tracking_history back to its pre-fix state (no sheriff roles at all)
        DB::statement("
            ALTER TABLE document_tracking_history
            MODIFY COLUMN to_role ENUM(
                'admin','malsu','case_management','records',
                'province_albay','province_camarines_sur','province_camarines_norte',
                'province_catanduanes','province_masbate','province_sorsogon'
            ) NOT NULL
        ");

        DB::statement("
            ALTER TABLE document_tracking_history
            MODIFY COLUMN from_role ENUM(
                'admin','malsu','case_management','records',
                'province_albay','province_camarines_sur','province_camarines_norte',
                'province_catanduanes','province_masbate','province_sorsogon'
            ) NULL
        ");

        DB::statement("
            ALTER TABLE document_tracking
            MODIFY COLUMN current_role ENUM(
                'admin','malsu','case_management','records',
                'province_albay','province_camarines_sur','province_camarines_norte',
                'province_catanduanes','province_masbate','province_sorsogon',
                'sheriff_albay','sheriff_camarines_sur','sheriff_camarines_norte',
                'sheriff_catanduanes','sheriff_masbate','sheriff_sorsogon'
            ) NOT NULL
        ");

        DB::statement("
            ALTER TABLE users
            MODIFY COLUMN role ENUM(
                'admin','user','malsu','case_management','records',
                'province_albay','province_camarines_sur','province_camarines_norte',
                'province_catanduanes','province_masbate','province_sorsogon',
                'sheriff_albay','sheriff_camarines_sur','sheriff_camarines_norte',
                'sheriff_catanduanes','sheriff_masbate','sheriff_sorsogon'
            ) NOT NULL DEFAULT 'user'
        ");
    }
};