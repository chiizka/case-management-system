<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // For MySQL/MariaDB, we need to alter the enum
        DB::statement("ALTER TABLE document_tracking_history MODIFY COLUMN to_role ENUM(
            'admin',
            'malsu',
            'case_management',
            'records',
            'province_albay',
            'province_camarines_sur',
            'province_camarines_norte',
            'province_catanduanes',
            'province_masbate',
            'province_sorsogon'
        )");
    }

    public function down()
    {
        DB::statement("ALTER TABLE document_tracking_history MODIFY COLUMN to_role ENUM(
            'admin',
            'malsu',
            'case_management',
            'province',
            'records'
        )");
    }
};