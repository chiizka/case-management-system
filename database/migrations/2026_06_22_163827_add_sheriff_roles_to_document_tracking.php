<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        DB::statement("ALTER TABLE document_tracking MODIFY COLUMN current_role ENUM(
            'admin',
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
        ) NOT NULL");
    }

    public function down()
    {
        DB::statement("ALTER TABLE document_tracking MODIFY COLUMN current_role ENUM(
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
        ) NOT NULL");
    }
};