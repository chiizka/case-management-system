<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop old tables if they exist
        Schema::dropIfExists('document_tracking_history');
        Schema::dropIfExists('document_tracking');

        // Create new document tracking table with role-based system
        Schema::create('document_tracking', function (Blueprint $table) {
            $table->id();
            $table->foreignId('case_id')->constrained('cases')->onDelete('cascade');
            
            // Role-based tracking
            $table->enum('current_role', ['admin', 'malsu', 'case_management', 'province']);
            $table->enum('status', ['Pending Receipt', 'Received', 'Transferred'])->default('Pending Receipt');
            
            // Who transferred it
            $table->foreignId('transferred_by_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('transferred_at')->nullable();
            
            // Who received it (from the target role)
            $table->foreignId('received_by_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('received_at')->nullable();
            
            $table->text('transfer_notes')->nullable();
            $table->timestamps();
        });

        // History table to track all movements
        Schema::create('document_tracking_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_tracking_id')->constrained('document_tracking')->onDelete('cascade');
            
            $table->enum('from_role', ['admin', 'malsu', 'case_management', 'province'])->nullable();
            $table->enum('to_role', ['admin', 'malsu', 'case_management', 'province']);
            
            $table->foreignId('transferred_by_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('transferred_at')->nullable();
            
            $table->foreignId('received_by_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('received_at')->nullable();
            
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_tracking_history');
        Schema::dropIfExists('document_tracking');
    }
};