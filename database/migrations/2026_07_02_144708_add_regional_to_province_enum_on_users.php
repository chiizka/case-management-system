<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE users MODIFY province ENUM(
            'regional',
            'albay',
            'camarines_sur',
            'camarines_norte',
            'catanduanes',
            'masbate',
            'sorsogon'
        ) NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE users MODIFY province ENUM(
            'albay',
            'camarines_sur',
            'camarines_norte',
            'catanduanes',
            'masbate',
            'sorsogon'
        ) NULL");
    }
};