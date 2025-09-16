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
        Schema::create('appeals_and_resolution', function (Blueprint $table) {
            $table->id();
            $table->foreignId('case_id')->constrained('cases')->onDelete('cascade');
            $table->date('date_returned_case_mgmt')->nullable();
            $table->string('review_ct_cnpc')->nullable();
            $table->date('date_received_drafter_finalization_2nd')->nullable();
            $table->date('date_returned_case_mgmt_signature_2nd')->nullable();
            $table->date('date_order_2nd_cnpc')->nullable();
            $table->date('released_date_2nd_cnpc')->nullable();
            $table->date('date_forwarded_malsu')->nullable();
            $table->date('motion_reconsideration_date')->nullable();
            $table->date('date_received_malsu')->nullable();
            $table->date('date_resolution_mr')->nullable();
            $table->date('released_date_resolution_mr')->nullable();
            $table->date('date_appeal_received_records')->nullable();
            $table->timestamps();

            // Add foreign key constraint if you have a cases table
            // $table->foreign('case_id')->references('id')->on('cases')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appeals_and_resolution');
    }
};