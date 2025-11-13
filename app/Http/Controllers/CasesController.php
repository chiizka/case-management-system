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
                        $query->where('overall_status', '!=', 'Completed');
                    })
                    ->get();

                    $html = view('frontend.partials.inspection_table', ['inspections' => $data])->render();
                    break;

                case '2':
                    $data = Docketing::with([
                        'case:id,inspection_id,case_no,establishment_name,current_stage,overall_status'
                    ])
                    ->whereHas('case', function($query) {
                        $query->where('overall_status', '!=', 'Completed');
                    })
                    ->get();

                    $html = view('frontend.partials.docketing_table', ['docketing' => $data])->render();
                    break;

                case '3':
                    $data = HearingProcess::with([
                        'case:id,inspection_id,case_no,establishment_name,current_stage,overall_status'
                    ])
                    ->whereHas('case', function($query) {
                        $query->where('overall_status', '!=', 'Completed');
                    })
                    ->get();

                    $html = view('frontend.partials.hearing_table', ['hearingProcess' => $data])->render();
                    break;

                case '4':
                    $data = ReviewAndDrafting::with([
                        'case:id,inspection_id,case_no,establishment_name,current_stage,overall_status'
                    ])
                    ->whereHas('case', function($query) {
                        $query->where('overall_status', '!=', 'Completed');
                    })
                    ->get();

                    $html = view('frontend.partials.review_table', ['reviewAndDrafting' => $data])->render();
                    break;

                case '5':
                    $data = OrderAndDisposition::with([
                        'case:id,inspection_id,case_no,establishment_name,current_stage,overall_status'
                    ])
                    ->whereHas('case', function($query) {
                        $query->where('overall_status', '!=', 'Completed');
                    })
                    ->get();

                    $html = view('frontend.partials.orders_table', ['ordersAndDisposition' => $data])->render();
                    break;

                case '6':
                    $data = ComplianceAndAward::with([
                        'case:id,inspection_id,case_no,establishment_name,current_stage,overall_status'
                    ])
                    ->whereHas('case', function($query) {
                        $query->where('overall_status', '!=', 'Completed');
                    })
                    ->get();

                    $html = view('frontend.partials.compliance_table', ['complianceAndAwards' => $data])->render();
                    break;

                case '7':
                    $data = AppealsAndResolution::with([
                        'case:id,inspection_id,case_no,establishment_name,current_stage,overall_status'
                    ])
                    ->whereHas('case', function($query) {
                        $query->where('overall_status', '!=', 'Completed');
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
        Log::info('=== CASE CREATION STARTED ===');
        Log::info('Request data:', $request->all());

        try {
            $validated = $request->validate([
                'inspection_id' => 'required|string|max:255|unique:cases,inspection_id',
                'case_no' => 'nullable|string|max:255',
                'establishment_name' => 'required|string|max:255',
            ]);

            Log::info('Validation passed:', $validated);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation failed:', $e->errors());
            return redirect()->route('case.index')
                ->withErrors($e->errors())
                ->withInput()
                ->with('error', 'Validation failed: ' . implode(', ', array_map(fn($errors) => implode(', ', $errors), $e->errors())));
        }

        DB::beginTransaction();
        Log::info('Database transaction started');

        try {
            // Create the case with fixed values
            Log::info('Creating CaseFile record...');
            $case = CaseFile::create([
                'inspection_id' => $validated['inspection_id'],
                'case_no' => $validated['case_no'],
                'establishment_name' => $validated['establishment_name'],
                'current_stage' => '1: Inspections',
                'overall_status' => 'Active'
            ]);
            Log::info('CaseFile created successfully', ['id' => $case->id, 'inspection_id' => $case->inspection_id]);

            // Create Inspection record
            Log::info('Creating Inspection record...');
            $inspection = Inspection::create(['case_id' => $case->id]);
            Log::info('Inspection created', ['id' => $inspection->id]);

            // Create Docketing record
            Log::info('Creating Docketing record...');
            $docketing = Docketing::create(['case_id' => $case->id]);
            Log::info('Docketing created', ['id' => $docketing->id]);

            // Create HearingProcess record
            Log::info('Creating HearingProcess record...');
            $hearing = HearingProcess::create(['case_id' => $case->id]);
            Log::info('HearingProcess created', ['id' => $hearing->id]);

            // Create ReviewAndDrafting record - WITH MORE DEFAULT VALUES
            Log::info('Creating ReviewAndDrafting record...');
            try {
                $review = ReviewAndDrafting::create([
                    'case_id' => $case->id,
                    'draft_order_type' => null,
                    'applicable_draft_order' => 'N',
                    'po_pct' => null,
                    'aging_po_pct' => null,
                    'status_po_pct' => 'Pending', // Changed from empty string
                    'date_received_from_po' => null,
                    'reviewer_drafter' => null,
                    'date_received_by_reviewer' => null,
                    'date_returned_from_drafter' => null,
                    'aging_10_days_tssd' => null,
                    'status_reviewer_drafter' => 'Pending', // Changed from empty string
                    'draft_order_tssd_reviewer' => null,
                ]);
                Log::info('ReviewAndDrafting created', ['id' => $review->id]);
            } catch (\Exception $e) {
                Log::error('ReviewAndDrafting creation failed specifically');
                Log::error('Error: ' . $e->getMessage());
                throw $e; // Re-throw to be caught by outer catch
            }

            // Create OrderAndDisposition record
            Log::info('Creating OrderAndDisposition record...');
            try {
                $order = OrderAndDisposition::create([
                    'case_id' => $case->id,
                    'aging_2_days_finalization' => null,
                    'status_finalization' => null,
                    'pct_96_days' => null,
                    'date_signed_mis' => null,
                    'status_pct' => null,
                    'reference_date_pct' => null,
                    'aging_pct' => null,
                    'disposition_mis' => null,
                    'disposition_actual' => null,
                    'findings_to_comply' => null,
                    'date_of_order_actual' => null,
                    'released_date_actual' => null,
                ]);
                Log::info('OrderAndDisposition created', ['id' => $order->id]);
            } catch (\Exception $e) {
                Log::error('OrderAndDisposition creation failed specifically');
                Log::error('Error: ' . $e->getMessage());
                throw $e;
            }

            // Create ComplianceAndAward record
            Log::info('Creating ComplianceAndAward record...');
            try {
                $compliance = ComplianceAndAward::create([
                    'case_id' => $case->id,
                    'compliance_order_monetary_award' => null,
                    'osh_penalty' => null,
                    'affected_male' => null,
                    'affected_female' => null,
                    'first_order_dismissal_cnpc' => 0,
                    'tavable_less_than_10_workers' => 0,
                    'with_deposited_monetary_claims' => 0,
                    'amount_deposited' => null,
                    'with_order_payment_notice' => 0,
                    'status_all_employees_received' => null,
                    'status_case_after_first_order' => null,
                    'date_notice_finality_dismissed' => null,
                    'released_date_notice_finality' => null,
                    'updated_ticked_in_mis' => 0,
                    'second_order_drafter' => null,
                    'date_received_by_drafter_ct_cnpc' => null,
                ]);
                Log::info('ComplianceAndAward created', ['id' => $compliance->id]);
            } catch (\Exception $e) {
                Log::error('ComplianceAndAward creation failed specifically');
                Log::error('Error: ' . $e->getMessage());
                throw $e;
            }

            // Create AppealsAndResolution record
            Log::info('Creating AppealsAndResolution record...');
            try {
                $appeal = AppealsAndResolution::create([
                    'case_id' => $case->id,
                    'date_returned_case_mgmt' => null,
                    'review_ct_cnpc' => null,
                    'date_received_drafter_finalization_2nd' => null,
                    'date_returned_case_mgmt_signature_2nd' => null,
                    'date_order_2nd_cnpc' => null,
                    'released_date_2nd_cnpc' => null,
                    'date_forwarded_malsu' => null,
                    'motion_reconsideration_date' => null,
                    'date_received_malsu' => null,
                    'date_resolution_mr' => null,
                    'released_date_resolution_mr' => null,
                    'date_appeal_received_records' => null,
                ]);
                Log::info('AppealsAndResolution created', ['id' => $appeal->id]);
            } catch (\Exception $e) {
                Log::error('AppealsAndResolution creation failed specifically');
                Log::error('Error: ' . $e->getMessage());
                throw $e;
            }

            // Log the action
            Log::info('Logging activity...');
            ActivityLogger::logAction(
                'CREATE',
                'Case',
                $case->inspection_id,
                null,
                [
                    'establishment' => $case->establishment_name,
                    'stage' => $case->current_stage,
                    'note' => 'Created with all stage records'
                ]
            );
            Log::info('Activity logged successfully');

            DB::commit();
            Log::info('Transaction committed successfully');
            Log::info('=== CASE CREATION COMPLETED SUCCESSFULLY ===');

            return redirect()->route('case.index')->with('success', 'Case created successfully with all stage records!');
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('=== CASE CREATION FAILED ===');
            Log::error('Error type: ' . get_class($e));
            Log::error('Error message: ' . $e->getMessage());
            Log::error('Error code: ' . $e->getCode());
            Log::error('File: ' . $e->getFile());
            Log::error('Line: ' . $e->getLine());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            // Additional context logging
            if (isset($case)) {
                Log::error('Case was created before error', ['case_id' => $case->id]);
            }
            if (isset($inspection)) Log::error('Inspection record was created', ['id' => $inspection->id]);
            if (isset($docketing)) Log::error('Docketing record was created', ['id' => $docketing->id]);
            if (isset($hearing)) Log::error('HearingProcess record was created', ['id' => $hearing->id]);
            if (isset($review)) Log::error('ReviewAndDrafting record was created', ['id' => $review->id]);
            if (isset($order)) Log::error('OrderAndDisposition record was created', ['id' => $order->id]);
            if (isset($compliance)) Log::error('ComplianceAndAward record was created', ['id' => $compliance->id]);
            if (isset($appeal)) Log::error('AppealsAndResolution record was created', ['id' => $appeal->id]);
            
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
        
        // Get the stage from the request (sent from the button)
        $completingStage = $request->input('stage', $case->current_stage);
        
        Log::info('Move to next stage requested', [
            'case_id' => $case->id,
            'current_stage' => $case->current_stage,
            'completing_stage' => $completingStage,
            'overall_status' => $case->overall_status
        ]);
        
        // Check if completing from Appeals & Resolution (the final stage)
        if ($completingStage === 'Appeals & Resolution' || $completingStage === '7: Appeals & Resolution') {
            Log::info('Completing final stage - marking as Completed');
            
            $case->update([
                'overall_status' => 'Completed',
                'current_stage' => '7: Appeals & Resolution'
            ]);
            
            ActivityLogger::logAction(
                'COMPLETE',
                'Case',
                $case->inspection_id,
                'Case completed and archived',
                ['establishment' => $case->establishment_name]
            );
            
            DB::commit();
            Log::info('Case marked as completed successfully');
            
            return response()->json([
                'success' => true,
                'message' => 'Case completed and moved to archived cases!'
            ]);
        }
        
        // Define stage progression (for UI tracking only)
        $stageMap = [
            '1: Inspections' => '2: Docketing',
            '2: Docketing' => '3: Hearing',
            '3: Hearing' => '4: Review & Drafting',
            '4: Review & Drafting' => '5: Orders & Disposition',
            '5: Orders & Disposition' => '6: Compliance & Awards',
            '6: Compliance & Awards' => '7: Appeals & Resolution',
        ];
        
        $currentStage = $case->current_stage;
        $nextStage = $stageMap[$currentStage] ?? null;
        
        if (!$nextStage) {
            Log::warning('Invalid stage progression', [
                'current_stage' => $currentStage
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Invalid stage progression'
            ], 400);
        }
        
        // Normal stage progression (UI tracking only)
        $case->update([
            'current_stage' => $nextStage
        ]);
        
        ActivityLogger::logAction(
            'UPDATE',
            'Case',
            $case->inspection_id,
            "Stage updated to {$nextStage}",
            ['establishment' => $case->establishment_name]
        );
        
        DB::commit();
        Log::info('Stage updated successfully', ['new_stage' => $nextStage]);
        
        return response()->json([
            'success' => true,
            'message' => "Case stage updated to {$nextStage} successfully!"
        ]);
        
    } catch (\Exception $e) {
        DB::rollback();
        Log::error('Failed to move case to next stage', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'Failed to move case: ' . $e->getMessage()
        ], 500);
    }
}

/**
 * Inline update via AJAX
 */
public function inlineUpdate(Request $request, $id)
{
    DB::beginTransaction();
    try {
        $case = CaseFile::lockForUpdate()->findOrFail($id);
        $oldData = $case->toArray();
        
        // Only allow specific fields to be updated inline
        $allowedFields = ['inspection_id', 'case_no', 'establishment_name', 'current_stage', 'overall_status'];
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