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
        Schema::create('review_and_drafting', function (Blueprint $table) {
            $table->id();
            $table->foreignId('case_id')->constrained('cases')->onDelete('cascade');
            $table->string('draft_order_type');
            $table->enum('applicable_draft_order', ['Y', 'N']);

            $table->integer('po_pct');
            $table->integer('aging_po_pct');
            $table->enum('status_po_pct', ['Pending', 'Ongoing', 'Overdue', 'Completed']);

            $table->date('date_received_from_po');
            $table->string('reviewer_drafter')->nullable(); // store reviewer name
            $table->date('date_received_by_reviewer')->nullable();
            $table->date('date_returned_from_drafter')->nullable();

            $table->integer('aging_10_days_tssd')->nullable();
            $table->enum('status_reviewer_drafter', ['Pending','Ongoing','Returned','Approved','Overdue']);

            $table->string('draft_order_tssd_reviewer')->nullable(); // store reviewer name

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('review_and_drafting');
    }
};
