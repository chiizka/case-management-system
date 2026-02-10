<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\CaseComputations;

class CaseFile extends Model
{
    use HasFactory, CaseComputations;

    protected $table = 'cases';
    protected $guarded = [];
    
    protected $fillable = [
        // Core Information
        'no',
        'inspection_id',
        'case_no', 
        'establishment_name',
        'establishment_address',
        'mode',
        'current_stage',
        'overall_status',
        'po_office',
        'document_checklist',
        
        // Inspection Stage
        'date_of_inspection',
        'inspector_name',
        'inspector_authority_no',
        'date_of_nr',
        'lapse_20_day_period',  // ✅ COMPUTED
        
        // Docketing Stage
        'pct_for_docketing',  // ✅ COMPUTED
        'date_scheduled_docketed',
        'aging_docket',  // ✅ COMPUTED
        'status_docket',  // ✅ COMPUTED
        'hearing_officer_mis',
        
        // Hearing Process Stage
        'date_1st_mc_actual',
        'first_mc_pct',  // ✅ COMPUTED
        'status_1st_mc',  // ✅ COMPUTED
        'date_2nd_last_mc',
        'second_last_mc_pct',  // ✅ COMPUTED
        'status_2nd_mc',  // ✅ COMPUTED
        'case_folder_forwarded_to_ro',
        'draft_order_from_po_type',
        'applicable_draft_order',
        'complete_case_folder',
        'twg_ali',
        
        // Review & Drafting Stage
        'po_pct',  // ✅ COMPUTED
        'aging_po_pct',  // ✅ COMPUTED
        'status_po_pct',  // ✅ COMPUTED
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
        
        // Orders & Disposition Stage
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
        
        // Compliance & Awards Stage
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
        
        // Appeals & Resolution Stage (2nd Order)
        'second_order_drafter',
        'date_received_by_drafter_ct_cnpc',
        'date_returned_case_mgmt_ct_cnpc',
        'review_ct_cnpc',
        'date_received_drafter_finalization_2nd',
        'date_returned_case_mgmt_signature_2nd',
        'date_order_2nd_cnpc',
        'released_date_2nd_cnpc',
        'scanned_order_2nd_cnpc',
        
        // Appeals & Resolution Stage (MALSU)
        'date_forwarded_malsu',
        'scanned_indorsement_malsu',
        'motion_reconsideration_date',
        'date_received_malsu',
        'date_resolution_mr',
        'released_date_resolution_mr',
        'scanned_resolution_mr',
        'date_appeal_received_records',
        'date_indorsed_office_secretary',
        
        // Additional Information
        'logbook_page_number',
        'remarks_notes',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        // Dates
        'date_of_inspection' => 'date',
        'date_of_nr' => 'date',
        'lapse_20_day_period' => 'date',  // ✅ COMPUTED
        'pct_for_docketing' => 'date',  // ✅ COMPUTED (changed from string)
        'date_scheduled_docketed' => 'date',
        'date_1st_mc_actual' => 'date',
        'date_2nd_last_mc' => 'date',
        'case_folder_forwarded_to_ro' => 'date',  // ✅ Added cast
        'po_pct' => 'date',  // ✅ COMPUTED (changed from string)
        'date_received_from_po' => 'date',
        'date_received_by_reviewer' => 'date',
        'date_returned_from_drafter' => 'date',
        'final_review_date_received' => 'date',
        'date_received_drafter_finalization' => 'date',
        'date_returned_case_mgmt_signature' => 'date',
        'date_signed_mis' => 'date',
        'reference_date_pct' => 'date',
        'date_of_order_actual' => 'date',
        'released_date_actual' => 'date',
        'date_notice_finality_dismissed' => 'date',
        'released_date_notice_finality' => 'date',
        'date_received_by_drafter_ct_cnpc' => 'date',
        'date_returned_case_mgmt_ct_cnpc' => 'date',
        'date_received_drafter_finalization_2nd' => 'date',
        'date_returned_case_mgmt_signature_2nd' => 'date',
        'date_order_2nd_cnpc' => 'date',
        'released_date_2nd_cnpc' => 'date',
        'date_forwarded_malsu' => 'date',
        'motion_reconsideration_date' => 'date',
        'date_received_malsu' => 'date',
        'date_resolution_mr' => 'date',
        'released_date_resolution_mr' => 'date',
        'date_appeal_received_records' => 'date',
        'date_indorsed_office_secretary' => 'date',
        
        // Integers
        'no' => 'integer',
        'aging_docket' => 'integer',  // ✅ COMPUTED
        'first_mc_pct' => 'integer',  // ✅ COMPUTED (changed from string)
        'second_last_mc_pct' => 'integer',  // ✅ COMPUTED (changed from string)
        'aging_po_pct' => 'integer',  // ✅ COMPUTED
        'aging_10_days_tssd' => 'integer',
        'aging_2_days_finalization' => 'integer',
        'aging_pct' => 'integer',
        'affected_male' => 'integer',
        'affected_female' => 'integer',
        
        // Decimals (Monetary)
        'compliance_order_monetary_award' => 'decimal:2',
        'osh_penalty' => 'decimal:2',
        'amount_deposited' => 'decimal:2',
        
        // Booleans (0/1)
        'first_order_dismissal_cnpc' => 'boolean',
        'tavable_less_than_10_workers' => 'boolean',
        'with_deposited_monetary_claims' => 'boolean',
        'with_order_payment_notice' => 'boolean',
        'updated_ticked_in_mis' => 'boolean',
        
        'document_checklist' => 'array',
    ];

    /**
     * Boot method to automatically compute fields when saving
     */
    protected static function boot()
    {
        parent::boot();
        
        // Automatically compute fields before creating
        static::creating(function ($case) {
            $case->computeFields();
        });
        
        // Automatically recompute fields before updating
        static::updating(function ($case) {
            $case->computeFields();
        });
    }

    // Relationships
    public function documentTracking()
    {
        return $this->hasOne(DocumentTracking::class, 'case_id');
    }

    public function inspections()
    {
        return $this->hasMany(Inspection::class, 'case_id');
    }
    
    public function appeal()
    {
        return $this->hasOne(CaseAppeal::class, 'case_id');
    }
}