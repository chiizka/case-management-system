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
        // Main document tracking table
        Schema::create('document_tracking', function (Blueprint $table) {
            $table->id();
            $table->foreignId('case_id')->constrained('cases')->onDelete('cascade');
            $table->string('current_location');
            $table->string('received_by');
            $table->date('date_received');
            $table->enum('status', ['Active', 'Pending Transfer', 'Archived'])->default('Active');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // History table to track all movements
        Schema::create('document_tracking_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_tracking_id')->constrained('document_tracking')->onDelete('cascade');
            $table->string('location');
            $table->string('received_by');
            $table->date('date_received');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_tracking_history');
        Schema::dropIfExists('document_tracking');
    }
};