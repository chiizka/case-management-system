<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateUserIdForeignKeyOnLogs extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop the existing foreign key
        Schema::table('logs', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        // Add the new foreign key with onDelete('set null')
        Schema::table('logs', function (Blueprint $table) {
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop the new foreign key
        Schema::table('logs', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        // Re-add the original foreign key with onDelete('cascade')
        Schema::table('logs', function (Blueprint $table) {
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
        });
    }
}

