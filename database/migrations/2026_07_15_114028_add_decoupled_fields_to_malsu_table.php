<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE malsu MODIFY case_id BIGINT UNSIGNED NULL');

        Schema::table('malsu', function (Blueprint $table) {
            $table->string('case_title')->nullable()->after('case_id');
        });
    }

    public function down(): void
    {
        Schema::table('malsu', function (Blueprint $table) {
            $table->dropColumn('case_title');
        });

        DB::statement('ALTER TABLE malsu MODIFY case_id BIGINT UNSIGNED NOT NULL');
    }
};