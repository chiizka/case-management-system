<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Safest way: change to string temporarily, then back to expanded enum
        Schema::table('document_tracking_history', function (Blueprint $table) {
            $table->string('from_role', 50)->nullable()->change();
        });

        // Now set the full enum (match your User::VALID_ROLES)
        DB::statement("ALTER TABLE document_tracking_history MODIFY from_role ENUM(
            'admin', 'malsu', 'case_management', 'records',
            'province_albay', 'province_camarines_sur', 'province_camarines_norte',
            'province_catanduanes', 'province_masbate', 'province_sorsogon'
        ) NULL");
    }

    public function down(): void
    {
        // Revert to old limited enum
        DB::statement("ALTER TABLE document_tracking_history MODIFY from_role ENUM(
            'admin', 'malsu', 'case_management', 'province', 'records'
        ) NULL");
    }
};