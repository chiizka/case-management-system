<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('cases', function (Blueprint $table) {
            // Display row number (separate from id)
            $table->integer('no')->nullable()->after('id');
            
            // Basic Information
            $table->string('po_office')->nullable()->after('overall_status');
            
            // Inspection Stage
            $table->date('date_of_inspection')->nullable();
            $table->string('inspector_name')->nullable();
            $table->string('inspector_authority_no')->nullable();
            $table->date('date_of_nr')->nullable();
            $table->date('lapse_20_day_period')->nullable();
            
            // Docketing Stage
            $table->date('pct_for_docketing')->nullable();
            $table->date('date_scheduled_docketed')->nullable();
            $table->integer('aging_docket')->nullable();
            $table->string('status_docket')->nullable();
            $table->string('hearing_officer_mis')->nullable();
            
            // Hearing Process Stage
            $table->date('date_1st_mc_actual')->nullable();
            $table->date('first_mc_pct')->nullable();
            $table->string('status_1st_mc')->nullable();
            $table->date('date_2nd_last_mc')->nullable();
            $table->date('second_last_mc_pct')->nullable();
            $table->string('status_2nd_mc')->nullable();
            $table->date('case_folder_forwarded_to_ro')->nullable();
            $table->string('draft_order_from_po_type')->nullable();
            $table->enum('applicable_draft_order', ['Y', 'N'])->nullable();
            $table->enum('complete_case_folder', ['Y', 'N'])->nullable();
            $table->string('twg_ali')->nullable();
            
            // Review & Drafting Stage
            $table->date('po_pct')->nullable();
            $table->integer('aging_po_pct')->nullable();
            $table->string('status_po_pct')->nullable();
            $table->date('date_received_from_po')->nullable();
            $table->string('reviewer_drafter')->nullable();
            $table->date('date_received_by_reviewer')->nullable();
            $table->date('date_returned_from_drafter')->nullable();
            $table->integer('aging_10_days_tssd')->nullable();
            $table->string('status_reviewer_drafter')->nullable();
            $table->string('draft_order_tssd_reviewer')->nullable();
            $table->date('final_review_date_received')->nullable();
            $table->date('date_received_drafter_finalization')->nullable();
            $table->date('date_returned_case_mgmt_signature')->nullable();
            $table->integer('aging_2_days_finalization')->nullable();
            $table->string('status_finalization')->nullable();
            
            // Orders & Disposition Stage
            $table->integer('pct_96_days')->nullable();
            $table->date('date_signed_mis')->nullable();
            $table->string('status_pct')->nullable();
            $table->date('reference_date_pct')->nullable();
            $table->integer('aging_pct')->nullable();
            $table->string('disposition_mis')->nullable();
            $table->string('disposition_actual')->nullable();
            $table->text('findings_to_comply')->nullable();
            $table->decimal('compliance_order_monetary_award', 15, 2)->nullable();
            $table->decimal('osh_penalty', 15, 2)->nullable();
            $table->integer('affected_male')->nullable();
            $table->integer('affected_female')->nullable();
            $table->date('date_of_order_actual')->nullable();
            $table->date('released_date_actual')->nullable();
            
            // Compliance & Awards Stage
            $table->enum('first_order_dismissal_cnpc', ['Y', 'N'])->nullable();
            $table->enum('tavable_less_than_10_workers', ['Y', 'N'])->nullable();
            $table->string('scanned_order_first')->nullable();
            $table->enum('with_deposited_monetary_claims', ['Y', 'N'])->nullable();
            $table->decimal('amount_deposited', 15, 2)->nullable();
            $table->enum('with_order_payment_notice', ['Y', 'N'])->nullable();
            $table->string('status_all_employees_received')->nullable();
            $table->string('status_case_after_first_order')->nullable();
            $table->date('date_notice_finality_dismissed')->nullable();
            $table->date('released_date_notice_finality')->nullable();
            $table->string('scanned_notice_finality')->nullable();
            $table->enum('updated_ticked_in_mis', ['Y', 'N'])->nullable();
            
            // Appeals & Resolution Stage (2nd Order)
            $table->string('second_order_drafter')->nullable();
            $table->date('date_received_by_drafter_ct_cnpc')->nullable();
            $table->date('date_returned_case_mgmt_ct_cnpc')->nullable();
            $table->string('review_ct_cnpc')->nullable();
            $table->date('date_received_drafter_finalization_2nd')->nullable();
            $table->date('date_returned_case_mgmt_signature_2nd')->nullable();
            $table->date('date_order_2nd_cnpc')->nullable();
            $table->date('released_date_2nd_cnpc')->nullable();
            $table->string('scanned_order_2nd_cnpc')->nullable();
            
            // Appeals & Resolution Stage (MALSU)
            $table->date('date_forwarded_malsu')->nullable();
            $table->string('scanned_indorsement_malsu')->nullable();
            $table->date('motion_reconsideration_date')->nullable();
            $table->date('date_received_malsu')->nullable();
            $table->date('date_resolution_mr')->nullable();
            $table->date('released_date_resolution_mr')->nullable();
            $table->string('scanned_resolution_mr')->nullable();
            $table->date('date_appeal_received_records')->nullable();
            $table->date('date_indorsed_office_secretary')->nullable();
            
            // Additional Information
            $table->string('logbook_page_number')->nullable();
            $table->text('remarks_notes')->nullable();
        });
    }

    public function down()
    {
        Schema::table('cases', function (Blueprint $table) {
            $table->dropColumn([
                'no',
                'po_office',
                'date_of_inspection',
                'inspector_name',
                'inspector_authority_no',
                'date_of_nr',
                'lapse_20_day_period',
                'pct_for_docketing',
                'date_scheduled_docketed',
                'aging_docket',
                'status_docket',
                'hearing_officer_mis',
                'date_1st_mc_actual',
                'first_mc_pct',
                'status_1st_mc',
                'date_2nd_last_mc',
                'second_last_mc_pct',
                'status_2nd_mc',
                'case_folder_forwarded_to_ro',
                'draft_order_from_po_type',
                'applicable_draft_order',
                'complete_case_folder',
                'twg_ali',
                'po_pct',
                'aging_po_pct',
                'status_po_pct',
                'date_received_from_po',
                'reviewer_drafter',
                'date_received_by_reviewer',
                'date_returned_from_drafter',
                'aging_10_days_tssd',
                'status_reviewer_drafter',
                'draft_order_tssd_reviewer',
                'final_review_date_received',
                'date_received_drafter_finalization',
                'date_returned_case_mgmt_signature',
                'aging_2_days_finalization',
                'status_finalization',
                'pct_96_days',
                'date_signed_mis',
                'status_pct',
                'reference_date_pct',
                'aging_pct',
                'disposition_mis',
                'disposition_actual',
                'findings_to_comply',
                'compliance_order_monetary_award',
                'osh_penalty',
                'affected_male',
                'affected_female',
                'date_of_order_actual',
                'released_date_actual',
                'first_order_dismissal_cnpc',
                'tavable_less_than_10_workers',
                'scanned_order_first',
                'with_deposited_monetary_claims',
                'amount_deposited',
                'with_order_payment_notice',
                'status_all_employees_received',
                'status_case_after_first_order',
                'date_notice_finality_dismissed',
                'released_date_notice_finality',
                'scanned_notice_finality',
                'updated_ticked_in_mis',
                'second_order_drafter',
                'date_received_by_drafter_ct_cnpc',
                'date_returned_case_mgmt_ct_cnpc',
                'review_ct_cnpc',
                'date_received_drafter_finalization_2nd',
                'date_returned_case_mgmt_signature_2nd',
                'date_order_2nd_cnpc',
                'released_date_2nd_cnpc',
                'scanned_order_2nd_cnpc',
                'date_forwarded_malsu',
                'scanned_indorsement_malsu',
                'motion_reconsideration_date',
                'date_received_malsu',
                'date_resolution_mr',
                'released_date_resolution_mr',
                'scanned_resolution_mr',
                'date_appeal_received_records',
                'date_indorsed_office_secretary',
                'logbook_page_number',
                'remarks_notes',
            ]);
        });
    }
};