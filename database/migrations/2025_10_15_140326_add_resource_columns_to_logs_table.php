<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('logs', function (Blueprint $table) {
            // Add new columns
            $table->string('resource_type', 100)->nullable()->after('action');
            $table->string('resource_id', 100)->nullable()->after('resource_type');
            $table->text('description')->nullable()->after('resource_id');
            
            // Add indexes for better performance
            $table->index('action');
            $table->index('resource_type');
            $table->index(['resource_type', 'resource_id']);
            $table->index('created_at');
        });

        // Migrate existing data: copy 'activity' to 'description'
        DB::table('logs')->whereNotNull('activity')->update([
            'description' => DB::raw('activity')
        ]);
    }

    public function down(): void
    {
        Schema::table('logs', function (Blueprint $table) {
            $table->dropIndex(['logs_action_index']);
            $table->dropIndex(['logs_resource_type_index']);
            $table->dropIndex(['logs_resource_type_resource_id_index']);
            $table->dropIndex(['logs_created_at_index']);
            
            $table->dropColumn(['resource_type', 'resource_id', 'description']);
        });
    }
};