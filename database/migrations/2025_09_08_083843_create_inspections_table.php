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
        Schema::create('inspections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('case_id')->constrained('cases')->onDelete('cascade');
            $table->string('inspection_id');
            $table->string('name_of_establishment');
            $table->string('po_office')->nullable();
            $table->string('inspector_name')->nullable();
            $table->string('inspector_authority_no')->nullable();
            $table->date('date_of_inspection')->nullable();
            $table->date('date_of_nr')->nullable();
            $table->date('lapse_20_day_period')->nullable()->storedAs('DATE_ADD(date_of_inspection, INTERVAL 20 DAY)');
            $table->string('twg_ali')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inspections');
    }
};