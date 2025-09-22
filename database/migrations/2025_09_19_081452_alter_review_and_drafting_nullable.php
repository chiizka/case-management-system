<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('review_and_drafting', function (Blueprint $table) {
            $table->string('draft_order_type')->nullable()->change();
            $table->integer('po_pct')->nullable()->change();
            $table->integer('aging_po_pct')->nullable()->change();
            $table->date('date_received_from_po')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('review_and_drafting', function (Blueprint $table) {
            $table->string('draft_order_type')->nullable(false)->change();
            $table->integer('po_pct')->nullable(false)->change();
            $table->integer('aging_po_pct')->nullable(false)->change();
            $table->date('date_received_from_po')->nullable(false)->change();
        });
    }
};
