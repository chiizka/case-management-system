<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CaseFile;
use App\Models\Inspection;
use App\Models\Docketing;
use App\Models\HearingProcess;
use App\Models\ReviewAndDrafting; 
use App\Models\OrderAndDisposition; 
use App\Models\ComplianceAndAward;
use App\Models\AppealsAndResolution;
use App\Helpers\ActivityLogger;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB; 
use App\Models\DocumentTracking;

class CasesController extends Controller
{
    /**
     * Get allowed tabs based on user role
     */
    private function getAllowedTabs()
    {
        $user = Auth::user();
        
        // Tab mapping: 1=Inspections, 2=Docketing, 3=Hearing, 4=Review&Drafting, 5=Orders&Disposition, 6=Compliance&Awards, 7=Appeals&Resolution
        $tabPermissions = [
            'admin' => [1, 2, 3, 4, 5, 6, 7],           // Admin sees all
            'malsu' => [7],                             // MALSU sees Appeals
            'province' => [1, 2, 3],                    // Province sees Inspections, Docketing, Hearing
            'case_management' => [4, 5, 6],             // Case Management sees Review, Orders, Compliance
        ];

        // Return allowed tabs for user's role, default to empty if role not found
        return $tabPermissions[$user->role] ?? [];
    }

    /**
     * Check if user can access specific tab
     */
    private function canAccessTab($tabNumber)
    {
        return in_array($tabNumber, $this->getAllowedTabs());
    }

    /**
     * Display the main case management page
     */
    public function index()
    {
        // Eager load to prevent N+1 on the main tab too
        $cases = CaseFile::select('id', 'inspection_id', 'case_no', 'establishment_name', 'current_stage', 'overall_status', 'created_at')
            ->where('overall_status', '!=', 'Completed')
            ->orderBy('created_at', 'desc')
            ->get();

        $allowedTabs = $this->getAllowedTabs();

        return view('frontend.case', [
            'cases' => $cases,
            'allowedTabs' => $allowedTabs,
            'inspections' => collect([]),
            'docketing' => collect([]),
            'hearingProcess' => collect([]),
            'reviewAndDrafting' => collect([]),
            'ordersAndDisposition' => collect([]),
            'complianceAndAwards' => collect([]),
            'appealsAndResolutions' => collect([])
        ]);
    }

    /**
     * Load tab data via AJAX
     */
    public function loadTabData(Request $request, $tabNumber)
    {
        // Check if user has access to this tab
        if (!$this->canAccessTab($tabNumber)) {
            return response()->json([
                'success' => false,
                'error' => 'You do not have access to this tab.'
            ], 403);
        }

        try {
            $data = null;
            $html = '';

            switch ($tabNumber) {
                case '1':
                    $data = Inspection::with([
                        'case:id,inspection_id,case_no,establishment_name,current_stage,overall_status'
                    ])
                    ->whereHas('case', function($query) {
                        $query->where('current_stage', '1: Inspections')
                            ->where('overall_status', '!=', 'Completed');
                    })
                    ->get();

                    $html = view('frontend.partials.inspection_table', ['inspections' => $data])->render();
                    break;

                case '2':
                    $data = Docketing::with([
                        'case:id,inspection_id,case_no,establishment_name,current_stage,overall_status'
                    ])
                    ->whereHas('case', function($query) {
                        $query->where('current_stage', '2: Docketing')
                            ->where('overall_status', '!=', 'Completed');
                    })
                    ->get();

                    $html = view('frontend.partials.docketing_table', ['docketing' => $data])->render();
                    break;

                case '3':
                    $data = HearingProcess::with([
                        'case:id,inspection_id,case_no,establishment_name,current_stage,overall_status'
                    ])
                    ->whereHas('case', function($query) {
                        $query->where('current_stage', '3: Hearing')
                            ->where('overall_status', '!=', 'Completed');
                    })
                    ->get();

                    $html = view('frontend.partials.hearing_table', ['hearingProcess' => $data])->render();
                    break;

                case '4':
                    $data = ReviewAndDrafting::with([
                        'case:id,inspection_id,case_no,establishment_name,current_stage,overall_status'
                    ])
                    ->whereHas('case', function($query) {
                        $query->where('current_stage', '4: Review & Drafting')
                            ->where('overall_status', '!=', 'Completed');
                    })
                    ->get();

                    $html = view('frontend.partials.review_table', ['reviewAndDrafting' => $data])->render();
                    break;

                case '5':
                    $data = OrderAndDisposition::with([
                        'case:id,inspection_id,case_no,establishment_name,current_stage,overall_status'
                    ])
                    ->whereHas('case', function($query) {
                        $query->where('current_stage', '5: Orders & Disposition')
                            ->where('overall_status', '!=', 'Completed');
                    })
                    ->get();

                    $html = view('frontend.partials.orders_table', ['ordersAndDisposition' => $data])->render();
                    break;

                case '6':
                    $data = ComplianceAndAward::with([
                        'case:id,inspection_id,case_no,establishment_name,current_stage,overall_status'
                    ])
                    ->whereHas('case', function($query) {
                        $query->where('current_stage', '6: Compliance & Awards')
                            ->where('overall_status', '!=', 'Completed');
                    })
                    ->get();

                    $html = view('frontend.partials.compliance_table', ['complianceAndAwards' => $data])->render();
                    break;

                case '7':
                    $data = AppealsAndResolution::with([
                        'case:id,inspection_id,case_no,establishment_name,current_stage,overall_status'
                    ])
                    ->whereHas('case', function($query) {
                        $query->where('current_stage', '7: Appeals & Resolution')
                            ->where('overall_status', '!=', 'Completed');
                    })
                    ->get();

                    $html = view('frontend.partials.appeals_table', ['appealsAndResolutions' => $data])->render();
                    break;

                default:
                    return response()->json([
                        'success' => false,
                        'error' => 'Invalid tab number'
                    ], 400);
            }

            return response()->json([
                'success' => true,
                'html' => $html,
                'count' => $data ? $data->count() : 0
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error loading tab data: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Failed to load data. Please try again.'
            ], 500);
        }
    }

/**
 * Store new case
 */
public function store(Request $request)
{
    $validated = $request->validate([
        'inspection_id' => 'required|string|max:255|unique:cases,inspection_id',
        'case_no' => 'nullable|string|max:255',
        'establishment_name' => 'required|string|max:255',
        'current_stage' => 'required|in:1: Inspections,2: Docketing,3: Hearing,4: Review & Drafting,5: Orders & Disposition,6: Compliance & Awards,7: Appeals & Resolution',
        'overall_status' => 'required|in:Active,Completed,Dismissed',
    ]);

    DB::beginTransaction();
    try {
        // Create the case
        $case = CaseFile::create($validated);

        // Create related inspection record if starting at inspections stage
        if ($case->current_stage === '1: Inspections') {
            Inspection::create(['case_id' => $case->id]);
        }

        // Log the action
        ActivityLogger::logAction(
            'CREATE',
            'Case',
            $case->inspection_id,
            null,
            [
                'establishment' => $case->establishment_name,
                'stage' => $case->current_stage
            ]
        );

        DB::commit();

        return redirect()->route('case.index')->with('success', 'Case created successfully!');
        
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Error creating case: ' . $e->getMessage());
        Log::error('Stack trace: ' . $e->getTraceAsString());
        
        return redirect()->route('case.index')
            ->with('error', 'Failed to create case: ' . $e->getMessage());
    }
}

/**
 * Update case
 */
public function update(Request $request, $id)
{
    $validated = $request->validate([
        'inspection_id' => 'required|string|max:255|unique:cases,inspection_id,' . $id,
        'case_no' => 'nullable|string|max:255',
        'establishment_name' => 'required|string|max:255',
        'current_stage' => 'required|in:1: Inspections,2: Docketing,3: Hearing,4: Review & Drafting,5: Orders & Disposition,6: Compliance & Awards,7: Appeals & Resolution',
        'overall_status' => 'required|in:Active,Completed,Dismissed',
    ]);

    DB::beginTransaction();
    try {
        $case = CaseFile::lockForUpdate()->findOrFail($id);
        $oldData = $case->toArray();
        
        $case->update($validated);

        $changes = $this->getChanges($oldData, $validated);

        ActivityLogger::logAction(
            'UPDATE',
            'Case',
            $case->inspection_id,
            "Updated: " . ($changes ?: 'No changes detected'),
            ['establishment' => $case->establishment_name]
        );

        DB::commit();

        return redirect()->route('case.index')->with('success', 'Case updated successfully!');
        
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Error updating case: ' . $e->getMessage());
        Log::error('Stack trace: ' . $e->getTraceAsString());
        
        return redirect()->route('case.index')
            ->with('error', 'Failed to update case: ' . $e->getMessage());
    }
}

/**
 * Delete case
 */
public function destroy($id)
{
    DB::beginTransaction();
    try {
        $case = CaseFile::lockForUpdate()->findOrFail($id);
        $caseInfo = $case->inspection_id . ' - ' . $case->establishment_name;
        $establishmentName = $case->establishment_name;
        
        // Delete the case (will cascade to related records if foreign keys are set up)
        $case->delete();
        
        ActivityLogger::logAction(
            'DELETE',
            'Case',
            $caseInfo,
            'Case deleted',
            ['establishment' => $establishmentName]
        );

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Case deleted successfully.'
        ]);
        
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Case delete failed: ' . $e->getMessage());
        Log::error('Stack trace: ' . $e->getTraceAsString());
        
        return response()->json([
            'success' => false,
            'message' => 'Failed to delete case.'
        ], 500);
    }
}

    /**
     * Show case
     */
    public function show($id)
    {
        try {
            $case = CaseFile::findOrFail($id);
            return response()->json([
                'success' => true,
                'data' => $case
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Case not found.'
            ], 404);
        }
    }

    /**
     * Edit case
     */
    public function edit($id)
    {
        try {
            $case = CaseFile::findOrFail($id);
            
            ActivityLogger::logAction(
                'VIEW',
                'Case',
                $case->inspection_id,
                'Opened case edit form',
                ['establishment' => $case->establishment_name]
            );
            
            return response()->json([
                'success' => true,
                'data' => $case
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Case not found.'
            ], 404);
        }
    }

/**
 * Move case to next stage with modal confirmation
 */
public function moveToNextStage(Request $request, $id)
{
    DB::beginTransaction();
    try {
        $case = CaseFile::findOrFail($id);
        
        // Define stage progression
        $stageMap = [
            '1: Inspections' => '2: Docketing',
            '2: Docketing' => '3: Hearing',
            '3: Hearing' => '4: Review & Drafting',
            '4: Review & Drafting' => '5: Orders & Disposition',
            '5: Orders & Disposition' => '6: Compliance & Awards',
            '6: Compliance & Awards' => '7: Appeals & Resolution',
            '7: Appeals & Resolution' => 'Completed'
        ];
        
        $currentStage = $case->current_stage;
        $nextStage = $stageMap[$currentStage] ?? null;
        
        if (!$nextStage) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid stage progression'
            ], 400);
        }
        
        // If completing the final stage (Appeals & Resolution)
        if ($currentStage === '7: Appeals & Resolution' && $nextStage === 'Completed') {
            // Update case status to Completed
            $case->update([
                'overall_status' => 'Completed',
                // Keep current_stage as is, or you can set it to a final stage
            ]);
            
            // No need to update document_tracking - it will automatically be filtered out
            // because the scope checks case.overall_status = 'Active'
            
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Case completed and moved to archived cases! Document removed from tracking.'
            ]);
        }
        
        // Normal stage progression
        $case->update([
            'current_stage' => $nextStage
        ]);
        
        DB::commit();
        return response()->json([
            'success' => true,
            'message' => "Case moved to {$nextStage} successfully!"
        ]);
        
    } catch (\Exception $e) {
        DB::rollback();
        Log::error('Failed to move case to next stage: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Failed to move case: ' . $e->getMessage()
        ], 500);
    }
}

public function inlineUpdate(Request $request, $id)
{
    DB::beginTransaction();
    try {
        $case = CaseFile::lockForUpdate()->findOrFail($id);
        $oldData = $case->toArray();
        
        // Allow ALL fields from the model to be updated inline
        $allowedFields = [
            // Core Information
            'no', 'inspection_id', 'case_no', 'establishment_name', 'current_stage', 
            'overall_status', 'po_office',
            
            // Inspection Stage
            'date_of_inspection', 'inspector_name', 'inspector_authority_no', 
            'date_of_nr', 'lapse_20_day_period',
            
            // Docketing Stage
            'pct_for_docketing', 'date_scheduled_docketed', 'aging_docket', 
            'status_docket', 'hearing_officer_mis',
            
            // Hearing Process Stage
            'date_1st_mc_actual', 'first_mc_pct', 'status_1st_mc', 
            'date_2nd_last_mc', 'second_last_mc_pct', 'status_2nd_mc',
            'case_folder_forwarded_to_ro', 'draft_order_from_po_type', 
            'applicable_draft_order', 'complete_case_folder', 'twg_ali',
            
            // Review & Drafting Stage
            'po_pct', 'aging_po_pct', 'status_po_pct', 'date_received_from_po',
            'reviewer_drafter', 'date_received_by_reviewer', 'date_returned_from_drafter',
            'aging_10_days_tssd', 'status_reviewer_drafter', 'draft_order_tssd_reviewer',
            'final_review_date_received', 'date_received_drafter_finalization',
            'date_returned_case_mgmt_signature', 'aging_2_days_finalization', 'status_finalization',
            
            // Orders & Disposition Stage
            'pct_96_days', 'date_signed_mis', 'status_pct', 'reference_date_pct',
            'aging_pct', 'disposition_mis', 'disposition_actual', 'findings_to_comply',
            'compliance_order_monetary_award', 'osh_penalty', 'affected_male', 'affected_female',
            'date_of_order_actual', 'released_date_actual',
            
            // Compliance & Awards Stage
            'first_order_dismissal_cnpc', 'tavable_less_than_10_workers', 'scanned_order_first',
            'with_deposited_monetary_claims', 'amount_deposited', 'with_order_payment_notice',
            'status_all_employees_received', 'status_case_after_first_order',
            'date_notice_finality_dismissed', 'released_date_notice_finality',
            'scanned_notice_finality', 'updated_ticked_in_mis',
            
            // Appeals & Resolution Stage (2nd Order)
            'second_order_drafter', 'date_received_by_drafter_ct_cnpc',
            'date_returned_case_mgmt_ct_cnpc', 'review_ct_cnpc',
            'date_received_drafter_finalization_2nd', 'date_returned_case_mgmt_signature_2nd',
            'date_order_2nd_cnpc', 'released_date_2nd_cnpc', 'scanned_order_2nd_cnpc',
            
            // Appeals & Resolution Stage (MALSU)
            'date_forwarded_malsu', 'scanned_indorsement_malsu', 'motion_reconsideration_date',
            'date_received_malsu', 'date_resolution_mr', 'released_date_resolution_mr',
            'scanned_resolution_mr', 'date_appeal_received_records', 'date_indorsed_office_secretary',
            
            // Additional Information
            'logbook_page_number', 'remarks_notes'
        ];
        
        $updateData = $request->only($allowedFields);
        
        if (empty($updateData)) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'No valid fields to update.'
            ], 422);
        }

        $case->update($updateData);
        $case->refresh();

        $changes = $this->getChanges($oldData, $updateData);

        ActivityLogger::logAction(
            'UPDATE',
            'Case',
            $case->inspection_id,
            "Inline updated: " . ($changes ?: 'No changes detected'),
            ['establishment' => $case->establishment_name]
        );

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Case updated successfully!',
            'data' => $case
        ]);
        
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Case inline update failed: ' . $e->getMessage());
        Log::error('Stack trace: ' . $e->getTraceAsString());
        
        return response()->json([
            'success' => false,
            'message' => 'Failed to update case: ' . $e->getMessage()
        ], 500);
    }
}

    /**
     * Helper function to format changes for logging
     */
    private function getChanges($oldData, $newData)
    {
        $changes = [];
        
        foreach ($newData as $key => $value) {
            if (!isset($oldData[$key]) || $oldData[$key] != $value) {
                $fieldName = ucfirst(str_replace('_', ' ', $key));
                $oldValue = $oldData[$key] ?? '(empty)';
                $newValue = $value ?? '(empty)';
                
                if ($key === 'current_stage') {
                    $oldValue = explode(': ', $oldValue)[1] ?? $oldValue;
                    $newValue = explode(': ', $newValue)[1] ?? $newValue;
                }
                
                $changes[] = "$fieldName: '$oldValue' → '$newValue'";
            }
        }

        return !empty($changes) ? implode(', ', $changes) : '';
    }


    public function getDocumentHistory($id)
    {
        try {
            $case = CaseFile::findOrFail($id);
            
            // Check if document tracking exists for this case
            $documentTracking = DocumentTracking::with(['history.transferredBy', 'history.receivedBy', 'transferredBy', 'receivedBy'])
                ->where('case_id', $case->id)
                ->first();
            
            if (!$documentTracking) {
                return response()->json([
                    'success' => true,
                    'has_tracking' => false,
                    'message' => 'No document tracking found for this case'
                ]);
            }
            
            $historyData = [];
            
            // Add current state
            $historyData[] = [
                'role' => DocumentTracking::ROLE_NAMES[$documentTracking->current_role] ?? $documentTracking->current_role,
                'status' => $documentTracking->status,
                'transferred_by' => $documentTracking->transferredBy 
                    ? $documentTracking->transferredBy->fname . ' ' . $documentTracking->transferredBy->lname 
                    : 'System',
                'transferred_at' => $documentTracking->transferred_at 
                    ? $documentTracking->transferred_at->format('M d, Y h:i A') 
                    : 'N/A',
                'received_by' => $documentTracking->receivedBy 
                    ? $documentTracking->receivedBy->fname . ' ' . $documentTracking->receivedBy->lname 
                    : 'Pending',
                'received_at' => $documentTracking->received_at 
                    ? $documentTracking->received_at->format('M d, Y h:i A') 
                    : 'Pending',
                'notes' => $documentTracking->transfer_notes,
                'time_ago' => $documentTracking->transferred_at 
                    ? $documentTracking->transferred_at->diffForHumans() 
                    : 'N/A'
            ];
            
            // Add historical records (oldest first for timeline display)
            foreach ($documentTracking->history()->orderBy('created_at', 'asc')->get() as $history) {
                $historyData[] = [
                    'role' => DocumentTracking::ROLE_NAMES[$history->to_role] ?? $history->to_role,
                    'from_role' => $history->from_role 
                        ? (DocumentTracking::ROLE_NAMES[$history->from_role] ?? $history->from_role)
                        : null,
                    'transferred_by' => $history->transferredBy 
                        ? $history->transferredBy->fname . ' ' . $history->transferredBy->lname 
                        : 'System',
                    'transferred_at' => $history->transferred_at 
                        ? $history->transferred_at->format('M d, Y h:i A') 
                        : 'N/A',
                    'received_by' => $history->receivedBy 
                        ? $history->receivedBy->fname . ' ' . $history->receivedBy->lname 
                        : 'Not Received',
                    'received_at' => $history->received_at 
                        ? $history->received_at->format('M d, Y h:i A') 
                        : 'N/A',
                    'notes' => $history->notes,
                    'time_ago' => $history->transferred_at 
                        ? $history->transferred_at->diffForHumans() 
                        : 'N/A'
                ];
            }
            
            return response()->json([
                'success' => true,
                'has_tracking' => true,
                'history' => array_reverse($historyData) // Reverse to show newest first
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error fetching document history: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load document history: ' . $e->getMessage()
            ], 500);
        }
    }
    
}