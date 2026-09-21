<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cases', function (Blueprint $table) {
            // Col 32 — MIS Status (Forwarded to RD's Account) — free text
            $table->string('mis_status_forwarded_rd')->nullable()->after('date_received_from_po');

            // Cols 39–42 — Final Review, 4 separate focal-person dates
            $table->date('final_review_focal1_date')->nullable()->after('draft_order_tssd_reviewer');
            $table->date('final_review_focal2_date')->nullable()->after('final_review_focal1_date');
            $table->date('final_review_focal3_date')->nullable()->after('final_review_focal2_date');
            $table->date('final_review_focal4_date')->nullable()->after('final_review_focal3_date');

            // Col 68 — Note tied to status_all_employees_received
            $table->text('status_all_employees_received_note')->nullable()->after('status_all_employees_received');

            // Cols 69–70 — tracking of 1st Order receipt
            $table->date('date_received_by_respondent')->nullable()->after('status_all_employees_received_note');
            $table->date('date_received_by_affected_employees')->nullable()->after('date_received_by_respondent');

            // Cols 76–77 — Evaluation
            $table->date('date_evaluated')->nullable()->after('updated_ticked_in_mis');
            $table->string('name_of_evaluator')->nullable()->after('date_evaluated');

            // Cols 81–84 — Review (C&T/CNPC), 4 separate focal-person dates
            $table->date('review_ctcnpc_focal1_date')->nullable()->after('date_returned_case_mgmt_ct_cnpc');
            $table->date('review_ctcnpc_focal2_date')->nullable()->after('review_ctcnpc_focal1_date');
            $table->date('review_ctcnpc_focal3_date')->nullable()->after('review_ctcnpc_focal2_date');
            $table->date('review_ctcnpc_focal4_date')->nullable()->after('review_ctcnpc_focal3_date');

            // Cols 100–101 — Archive indorsement
            $table->date('date_indorsed_to_records')->nullable()->after('date_indorsed_office_secretary');
            $table->string('scanned_copy_indorsement')->nullable()->after('date_indorsed_to_records');
        });
    }

    public function down(): void
    {
        Schema::table('cases', function (Blueprint $table) {
            $table->dropColumn([
                'mis_status_forwarded_rd',
                'final_review_focal1_date',
                'final_review_focal2_date',
                'final_review_focal3_date',
                'final_review_focal4_date',
                'status_all_employees_received_note',
                'date_received_by_respondent',
                'date_received_by_affected_employees',
                'date_evaluated',
                'name_of_evaluator',
                'review_ctcnpc_focal1_date',
                'review_ctcnpc_focal2_date',
                'review_ctcnpc_focal3_date',
                'review_ctcnpc_focal4_date',
                'date_indorsed_to_records',
                'scanned_copy_indorsement',
            ]);
        });
    }
};