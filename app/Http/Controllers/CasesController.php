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
use Illuminate\Support\Facades\Validator;

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
    // Load ALL columns from the cases table
    $cases = CaseFile::where('overall_status', '!=', 'Completed')
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

    private function getValidationRules()
        {
            return [
                // Core Information
                'no' => 'nullable|integer',
                'inspection_id' => 'nullable|string|max:255',
                'case_no' => 'nullable|string|max:255',
                'establishment_name' => 'nullable|string|max:255',
                'po_office' => 'nullable|string|max:255',
                'current_stage' => 'nullable|in:1: Inspections,2: Docketing,3: Hearing,4: Review & Drafting,5: Orders & Disposition,6: Compliance & Awards,7: Appeals & Resolution',
                'overall_status' => 'nullable|in:Active,Completed,Dismissed',
                
                // Inspection Stage
                'date_of_inspection' => 'nullable|date',
                'inspector_name' => 'nullable|string|max:255',
                'inspector_authority_no' => 'nullable|string|max:255',
                'date_of_nr' => 'nullable|date',
                'lapse_20_day_period' => 'nullable|string|max:255',
                
                // Docketing Stage
                'pct_for_docketing' => 'nullable|string|max:255',
                'date_scheduled_docketed' => 'nullable|date',
                'aging_docket' => 'nullable|integer',
                'status_docket' => 'nullable|string|max:255',
                'hearing_officer_mis' => 'nullable|string|max:255',
                
                // Hearing Process Stage
                'date_1st_mc_actual' => 'nullable|date',
                'first_mc_pct' => 'nullable|string|max:255',
                'status_1st_mc' => 'nullable|string|max:255',
                'date_2nd_last_mc' => 'nullable|date',
                'second_last_mc_pct' => 'nullable|string|max:255',
                'status_2nd_mc' => 'nullable|string|max:255',
                'case_folder_forwarded_to_ro' => 'nullable|string|max:255',
                'draft_order_from_po_type' => 'nullable|string|max:255',
                'applicable_draft_order' => 'nullable|in:Y,N',
                'complete_case_folder' => 'nullable|in:Y,N',
                'twg_ali' => 'nullable|string|max:255',
                
                // Review & Drafting Stage
                'po_pct' => 'nullable|string|max:255',
                'aging_po_pct' => 'nullable|integer',
                'status_po_pct' => 'nullable|string|max:255',
                'date_received_from_po' => 'nullable|date',
                'reviewer_drafter' => 'nullable|string|max:255',
                'date_received_by_reviewer' => 'nullable|date',
                'date_returned_from_drafter' => 'nullable|date',
                'aging_10_days_tssd' => 'nullable|integer',
                'status_reviewer_drafter' => 'nullable|string|max:255',
                'draft_order_tssd_reviewer' => 'nullable|string|max:255',
                'final_review_date_received' => 'nullable|date',
                'date_received_drafter_finalization' => 'nullable|date',
                'date_returned_case_mgmt_signature' => 'nullable|date',
                'aging_2_days_finalization' => 'nullable|integer',
                'status_finalization' => 'nullable|string|max:255',
                
                // Orders & Disposition Stage
                'pct_96_days' => 'nullable|string|max:255',
                'date_signed_mis' => 'nullable|date',
                'status_pct' => 'nullable|string|max:255',
                'reference_date_pct' => 'nullable|date',
                'aging_pct' => 'nullable|integer',
                'disposition_mis' => 'nullable|string|max:255',
                'disposition_actual' => 'nullable|string|max:255',
                'findings_to_comply' => 'nullable|string',
                'compliance_order_monetary_award' => 'nullable|numeric|min:0',
                'osh_penalty' => 'nullable|numeric|min:0',
                'affected_male' => 'nullable|integer|min:0',
                'affected_female' => 'nullable|integer|min:0',
                'date_of_order_actual' => 'nullable|date',
                'released_date_actual' => 'nullable|date',
                
                // Compliance & Awards Stage
                'first_order_dismissal_cnpc' => 'nullable|boolean',
                'tavable_less_than_10_workers' => 'nullable|boolean',
                'scanned_order_first' => 'nullable|string|max:255',
                'with_deposited_monetary_claims' => 'nullable|boolean',
                'amount_deposited' => 'nullable|numeric|min:0',
                'with_order_payment_notice' => 'nullable|boolean',
                'status_all_employees_received' => 'nullable|string|max:255',
                'status_case_after_first_order' => 'nullable|string|max:255',
                'date_notice_finality_dismissed' => 'nullable|date',
                'released_date_notice_finality' => 'nullable|date',
                'scanned_notice_finality' => 'nullable|string|max:255',
                'updated_ticked_in_mis' => 'nullable|boolean',
                
                // Appeals & Resolution Stage (2nd Order)
                'second_order_drafter' => 'nullable|string|max:255',
                'date_received_by_drafter_ct_cnpc' => 'nullable|date',
                'date_returned_case_mgmt_ct_cnpc' => 'nullable|date',
                'review_ct_cnpc' => 'nullable|string|max:255',
                'date_received_drafter_finalization_2nd' => 'nullable|date',
                'date_returned_case_mgmt_signature_2nd' => 'nullable|date',
                'date_order_2nd_cnpc' => 'nullable|date',
                'released_date_2nd_cnpc' => 'nullable|date',
                'scanned_order_2nd_cnpc' => 'nullable|string|max:255',
                
                // Appeals & Resolution Stage (MALSU)
                'date_forwarded_malsu' => 'nullable|date',
                'scanned_indorsement_malsu' => 'nullable|string|max:255',
                'motion_reconsideration_date' => 'nullable|date',
                'date_received_malsu' => 'nullable|date',
                'date_resolution_mr' => 'nullable|date',
                'released_date_resolution_mr' => 'nullable|date',
                'scanned_resolution_mr' => 'nullable|string|max:255',
                'date_appeal_received_records' => 'nullable|date',
                'date_indorsed_office_secretary' => 'nullable|date',
                
                // Additional Information
                'logbook_page_number' => 'nullable|string|max:255',
                'remarks_notes' => 'nullable|string',
            ];
        }

    /**
     * Update case with validation
     */
    public function inlineUpdate(Request $request, $id)
    {
        Log::info('=== INLINE UPDATE DEBUG ===');
        Log::info('Case ID: ' . $id);
        Log::info('Request Data:', $request->all());
        
        DB::beginTransaction();
        try {
            $case = CaseFile::lockForUpdate()->findOrFail($id);
            $oldData = $case->toArray();
            
            // Get only the fields that are being updated
            $updateData = $request->except(['_token', '_method', 'id']);
            
            // Validate the data
            $validated = $request->validate($this->getValidationRules());
            
            if (empty($updateData)) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'No valid fields to update.'
                ], 422);
            }

            // Convert boolean fields from string to actual boolean
            $booleanFields = [
                'first_order_dismissal_cnpc',
                'tavable_less_than_10_workers',
                'with_deposited_monetary_claims',
                'with_order_payment_notice',
                'updated_ticked_in_mis'
            ];
            
            foreach ($booleanFields as $field) {
                if (array_key_exists($field, $updateData)) {
                    $updateData[$field] = filter_var($updateData[$field], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                }
            }

            $case->update($updateData);
            $case->refresh();
            
            Log::info('Update successful. New data:', $case->toArray());

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
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            Log::error('Validation failed: ' . json_encode($e->errors()));
            
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
            
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
    
public function importCsv(Request $request)
{
    // Validate the uploaded file
    $validator = Validator::make($request->all(), [
        'csv_file' => 'required|file|mimes:csv,txt|max:5120', // 5MB max
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => 'Invalid file. Please upload a CSV file (max 5MB).',
        ], 422);
    }

    try {
        $file = $request->file('csv_file');
        $path = $file->getRealPath();
        
        // Open and read the CSV file
        $csvData = array_map('str_getcsv', file($path));
        
        // Get the header row (first row)
        $header = array_map('trim', $csvData[0]);
        $headerCount = count($header);
        
        Log::info('CSV Header Count: ' . $headerCount);
        Log::info('CSV Headers: ' . json_encode($header));
        
        // Remove the header (row 1) and the format row (row 2) from data
        unset($csvData[0]); // Remove header
        unset($csvData[1]); // Remove the format/example row (1234, dd/mm/yyyy, etc.)
        
        $successCount = 0;
        $errors = [];
        
        DB::beginTransaction();
        
        try {
            foreach ($csvData as $index => $row) {
                $rowNumber = $index + 1;
                
                // Skip empty rows
                if (empty(array_filter($row))) {
                    continue;
                }
                
                // Check if row has same number of columns as header
                $rowCount = count($row);
                if ($rowCount !== $headerCount) {
                    Log::warning("Row {$rowNumber} has {$rowCount} columns, expected {$headerCount}");
                    
                    // Pad row with empty values if it has fewer columns
                    if ($rowCount < $headerCount) {
                        $row = array_pad($row, $headerCount, '');
                    } 
                    // Trim row if it has more columns
                    else {
                        $row = array_slice($row, 0, $headerCount);
                    }
                }
                
                // Map CSV columns to database columns
                $data = array_combine($header, $row);
                
                // Check for required fields
                if (empty($data['Inspection ID']) || empty($data['Name of Establihsment'])) {
                    $errors[] = "Row {$rowNumber}: Missing required fields (Inspection ID or Establishment name)";
                    continue;
                }
                
                // Create case record
                try {
                    CaseFile::create([
                        // Map CSV headers to database columns
                        'no' => $data['NO.'] ?? null,
                        'po_office' => $data['PO'] ?? null,
                        'inspection_id' => $data['Inspection ID'] ?? null,
                        'establishment_name' => $data['Name of Establihsment'] ?? null,
                        'date_of_inspection' => !empty($data['Date of Inspection ']) ? $this->parseDate($data['Date of Inspection ']) : null,
                        'inspector_name' => $data['Name of Inspector'] ?? null,
                        'inspector_authority_no' => $data['Authority No.'] ?? null,
                        'date_of_nr' => !empty($data['Date of NR ']) ? $this->parseDate($data['Date of NR ']) : null,
                        'lapse_20_day_period' => $data['Lapse of 20 day Correction Period '] ?? null,
                        'pct_for_docketing' => $data['PCT for Docketing (within 5 days from the lapse of the Correction Period) '] ?? null,
                        'date_scheduled_docketed' => !empty($data['Date Scheduled/ Docketed']) ? $this->parseDate($data['Date Scheduled/ Docketed']) : null,
                        'aging_docket' => $data['Aging (Docket)'] ?? null,
                        'status_docket' => $data['Status (Docket)'] ?? null,
                        'case_no' => $data['Case No. '] ?? null,
                        'hearing_officer_mis' => $data['Hearing Officer (MIS)'] ?? null,
                        
                        // Set defaults for required fields
                        'current_stage' => '1: Inspections',
                        'overall_status' => 'Active',
                    ]);
                    $successCount++;
                } catch (\Exception $e) {
                    $errors[] = "Row {$rowNumber}: " . $e->getMessage();
                    Log::error("Row {$rowNumber} error: " . $e->getMessage());
                }
            }
            
            DB::commit();
            
            $message = "Successfully imported {$successCount} records";
            if (count($errors) > 0) {
                $message .= " with " . count($errors) . " errors";
            }
            
            return response()->json([
                'success' => true,
                'message' => $message,
                'success_count' => $successCount,
                'errors' => $errors
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
        
    } catch (\Exception $e) {
        Log::error('CSV Import Error: ' . $e->getMessage());
        Log::error('Stack trace: ' . $e->getTraceAsString());
        
        return response()->json([
            'success' => false,
            'message' => 'Error processing CSV: ' . $e->getMessage()
        ], 500);
    }
}

/**
 * Helper function to parse dates from CSV (handles dd/mm/yyyy format)
 */
private function parseDate($dateString)
{
    if (empty($dateString) || $dateString === '1234') {
        return null;
    }
    
    try {
        // Try to parse dd/mm/yyyy format
        $date = \DateTime::createFromFormat('d/m/Y', trim($dateString));
        if ($date) {
            return $date->format('Y-m-d');
        }
        
        // Fallback to strtotime
        return date('Y-m-d', strtotime($dateString));
    } catch (\Exception $e) {
        return null;
    }
}
}