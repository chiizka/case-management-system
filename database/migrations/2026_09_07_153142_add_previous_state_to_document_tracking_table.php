<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('document_tracking', function (Blueprint $table) {
            $table->string('previous_role')->nullable()->after('case_tag');
            $table->string('previous_status')->nullable()->after('previous_role');
            $table->unsignedBigInteger('previous_received_by_user_id')->nullable()->after('previous_status');
            $table->timestamp('previous_received_at')->nullable()->after('previous_received_by_user_id');
            $table->text('previous_transfer_notes')->nullable()->after('previous_received_at');
            $table->string('previous_case_tag')->nullable()->after('previous_transfer_notes');
        });
    }

    public function down()
    {
        Schema::table('document_tracking', function (Blueprint $table) {
            $table->dropColumn([
                'previous_role',
                'previous_status',
                'previous_received_by_user_id',
                'previous_received_at',
                'previous_transfer_notes',
                'previous_case_tag',
            ]);
        });
    }
};