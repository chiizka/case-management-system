<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('review_and_drafting', function (Blueprint $table) {
            $table->string('draft_order_tssd_reviewer')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('review_and_drafting', function (Blueprint $table) {
            $table->unsignedBigInteger('draft_order_tssd_reviewer')->nullable()->change();
        });
    }

};
