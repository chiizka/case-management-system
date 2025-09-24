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

            // text/enum fields
            $table->string('draft_order_type')->nullable();
            $table->enum('applicable_draft_order', ['Y', 'N'])->default('N');

            // numeric fields (nullable)
            $table->integer('po_pct')->nullable();
            $table->integer('aging_po_pct')->nullable();
            $table->enum('status_po_pct', ['Pending', 'Ongoing', 'Overdue', 'Completed'])->default('Pending');

            // date fields (nullable)
            $table->date('date_received_from_po')->nullable();
            $table->string('reviewer_drafter')->nullable();
            $table->date('date_received_by_reviewer')->nullable();
            $table->date('date_returned_from_drafter')->nullable();

            $table->integer('aging_10_days_tssd')->nullable();
            $table->enum('status_reviewer_drafter', ['Pending','Ongoing','Returned','Approved','Overdue'])->default('Pending');

            $table->string('draft_order_tssd_reviewer')->nullable();

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
