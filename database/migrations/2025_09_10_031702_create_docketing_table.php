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
        Schema::create('docketing', function (Blueprint $table) {
            $table->id(); // Auto-incrementing primary key
            $table->foreignId('case_id')->constrained('cases')->onDelete('cascade'); // Foreign key to cases table
            $table->decimal('pct_for_docketing', 8, 2)->nullable(); // Decimal for calculated field (e.g., percentage)
            $table->date('date_scheduled_docketed')->nullable(); // Date field
            $table->integer('aging_docket')->nullable(); // Integer for calculated aging field
            $table->string('status_docket', 255)->nullable(); // String field for status
            $table->string('hearing_officer_mis', 255)->nullable(); // String field for hearing officer
            $table->timestamps(); // created_at and updated_at columns
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('docketing');
    }
};