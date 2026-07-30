<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('malsu', function (Blueprint $table) {
            $table->foreignId('assigned_sheriff_user_id')
                ->nullable()
                ->after('sheriff_designate')
                ->constrained('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('malsu', function (Blueprint $table) {
            $table->dropForeign(['assigned_sheriff_user_id']);
            $table->dropColumn('assigned_sheriff_user_id');
        });
    }
};