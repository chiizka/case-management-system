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
        Schema::create('order_and_disposition', function (Blueprint $table) {
            $table->id();
            $table->foreignId('case_id')->constrained('cases')->onDelete('cascade');
            
            // Finalization fields
            $table->integer('aging_2_days_finalization')->nullable()->comment('Calculated field');
            $table->string('status_finalization')->nullable();
            
            // PCT fields
            $table->integer('pct_96_days')->nullable()->comment('Calculated field');
            $table->date('date_signed_mis')->nullable();
            $table->string('status_pct')->nullable();
            $table->date('reference_date_pct')->nullable();
            $table->integer('aging_pct')->nullable()->comment('Calculated field');
            
            // Disposition fields
            $table->string('disposition_mis')->nullable();
            $table->string('disposition_actual')->nullable();
            $table->longText('findings_to_comply')->nullable();
            
            // Order and release dates
            $table->date('date_of_order_actual')->nullable();
            $table->date('released_date_actual')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_and_disposition');
    }
};