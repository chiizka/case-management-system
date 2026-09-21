<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cases', function (Blueprint $table) {
            $table->dropColumn([
                'final_review_date_received',
                'review_ct_cnpc',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('cases', function (Blueprint $table) {
            $table->date('final_review_date_received')->nullable();
            $table->string('review_ct_cnpc')->nullable();
        });
    }
};