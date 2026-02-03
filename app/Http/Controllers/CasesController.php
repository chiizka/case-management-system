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
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Writer\Csv as CsvWriter;
use App\Models\User;

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
        $user = Auth::user();
        
        // Start with base query for active cases
        $query = CaseFile::where('overall_status', '!=', 'Completed')
            ->orderBy('created_at', 'desc');
        
        // âœ¨ FILTER: Provincial users only see cases currently located at their province.
        // Uses DocumentTracking.current_role (live location) instead of po_office (static origin).
        // This way cases disappear from the province view once transferred elsewhere.
        if ($user->isProvince()) {
            $query->whereHas('documentTracking', function ($q) use ($user) {
                $q->where('current_role', $user->role);
            });
        }
        // Admin and MALSU see all cases (no filter applied)
        
        $cases = $query->get();

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
            $user = Auth::user();
            $data = null;
            $html = '';

            // âœ¨ Create a reusable query filter function
            $applyProvinceFilter = function($query) use ($user) {
                if ($user->isProvince()) {
                    $provinceName = $user->getProvinceName();
                    $query->whereHas('case', function($q) use ($provinceName) {
                        $q->where('po_office', $provinceName);
                    });
                }
            };

            switch ($tabNumber) {
                case '1':
                    $data = Inspection::with([
                        'case:id,inspection_id,case_no,establishment_name,current_stage,overall_status,po_office'
                    ])
                    ->whereHas('case', function($query) use ($user) {
                        $query->where('current_stage', '1: Inspections')
                            ->where('overall_status', '!=', 'Completed');
                        
                        // âœ¨ Filter by current document location, not origin
                        if ($user->isProvince()) {
                            $query->whereHas('documentTracking', function ($q) use ($user) {
                                $q->where('current_role', $user->role);
                            });
                        }
                    })
                    ->get();

                    $html = view('frontend.partials.inspection_table', ['inspections' => $data])->render();
                    break;

                case '2':
                    $data = Docketing::with([
                        'case:id,inspection_id,case_no,establishment_name,current_stage,overall_status,po_office'
                    ])
                    ->whereHas('case', function($query) use ($user) {
                        $query->where('current_stage', '2: Docketing')
                            ->where('overall_status', '!=', 'Completed');
                        
                        // âœ¨ Filter by current document location, not origin
                        if ($user->isProvince()) {
                            $query->whereHas('documentTracking', function ($q) use ($user) {
                                $q->where('current_role', $user->role);
                            });
                        }
                    })
                    ->get();

                    $html = view('frontend.partials.docketing_table', ['docketing' => $data])->render();
                    break;

                case '3':
                    $data = HearingProcess::with([
                        'case:id,inspection_id,case_no,establishment_name,current_stage,overall_status,po_office'
                    ])
                    ->whereHas('case', function($query) use ($user) {
                        $query->where('current_stage', '3: Hearing')
                            ->where('overall_status', '!=', 'Completed');
                        
                        // âœ¨ Filter by current document location, not origin
                        if ($user->isProvince()) {
                            $query->whereHas('documentTracking', function ($q) use ($user) {
                                $q->where('current_role', $user->role);
                            });
                        }
                    })
                    ->get();

                    $html = view('frontend.partials.hearing_table', ['hearingProcess' => $data])->render();
                    break;

                case '4':
                    $data = ReviewAndDrafting::with([
                        'case:id,inspection_id,case_no,establishment_name,current_stage,overall_status,po_office'
                    ])
                    ->whereHas('case', function($query) use ($user) {
                        $query->where('current_stage', '4: Review & Drafting')
                            ->where('overall_status', '!=', 'Completed');
                        
                        // âœ¨ Filter by current document location, not origin
                        if ($user->isProvince()) {
                            $query->whereHas('documentTracking', function ($q) use ($user) {
                                $q->where('current_role', $user->role);
                            });
                        }
                    })
                    ->get();

                    $html = view('frontend.partials.review_table', ['reviewAndDrafting' => $data])->render();
                    break;

                case '5':
                    $data = OrderAndDisposition::with([
                        'case:id,inspection_id,case_no,establishment_name,current_stage,overall_status,po_office'
                    ])
                    ->whereHas('case', function($query) use ($user) {
                        $query->where('current_stage', '5: Orders & Disposition')
                            ->where('overall_status', '!=', 'Completed');
                        
                        // âœ¨ Filter by current document location, not origin
                        if ($user->isProvince()) {
                            $query->whereHas('documentTracking', function ($q) use ($user) {
                                $q->where('current_role', $user->role);
                            });
                        }
                    })
                    ->get();

                    $html = view('frontend.partials.orders_table', ['ordersAndDisposition' => $data])->render();
                    break;

                case '6':
                    $data = ComplianceAndAward::with([
                        'case:id,inspection_id,case_no,establishment_name,current_stage,overall_status,po_office'
                    ])
                    ->whereHas('case', function($query) use ($user) {
                        $query->where('current_stage', '6: Compliance & Awards')
                            ->where('overall_status', '!=', 'Completed');
                        
                        // âœ¨ Filter by current document location, not origin
                        if ($user->isProvince()) {
                            $query->whereHas('documentTracking', function ($q) use ($user) {
                                $q->where('current_role', $user->role);
                            });
                        }
                    })
                    ->get();

                    $html = view('frontend.partials.compliance_table', ['complianceAndAwards' => $data])->render();
                    break;

                case '7':
                    $data = AppealsAndResolution::with([
                        'case:id,inspection_id,case_no,establishment_name,current_stage,overall_status,po_office'
                    ])
                    ->whereHas('case', function($query) use ($user) {
                        $query->where('current_stage', '7: Appeals & Resolution')
                            ->where('overall_status', '!=', 'Completed');
                        
                        // âœ¨ Filter by current document location, not origin
                        if ($user->isProvince()) {
                            $query->whereHas('documentTracking', function ($q) use ($user) {
                                $q->where('current_role', $user->role);
                            });
                        }
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

public function store(Request $request)
{
    $validated = $request->validate([
        'inspection_id' => 'required|string|max:255|unique:cases,inspection_id',
        'case_no' => 'nullable|string|max:255',
        'establishment_name' => 'required|string|max:255',
        'establishment_address' => 'nullable|string',
        'mode' => 'nullable|string|max:255',
        'po_office' => 'required|string|max:255',  // â† Now required!
        'current_stage' => 'required|in:1: Inspections,2: Docketing,3: Hearing,4: Review & Drafting,5: Orders & Disposition,6: Compliance & Awards,7: Appeals & Resolution',
        'overall_status' => 'required|in:Active,Completed,Dismissed',
    ]);

    DB::beginTransaction();
    try {
        $user = Auth::user();
        
        // âœ¨ Verify province users can only create cases for their province
        if ($user->isProvince() && $validated['po_office'] !== $user->getProvinceName()) {
            throw new \Exception('You can only create cases for your own province office.');
        }
        
        // Create the case
        $case = CaseFile::create($validated);

        // Create related inspection record if starting at inspections stage
        if ($case->current_stage === '1: Inspections') {
            Inspection::create(['case_id' => $case->id]);
        }

        // âœ¨ Create initial document tracking
        $this->createInitialDocumentTracking($case, $user);

        // Log the action
        ActivityLogger::logAction(
            'CREATE',
            'Case',
            $case->inspection_id,
            "Created case at {$case->po_office}",
            [
                'establishment' => $case->establishment_name,
                'stage' => $case->current_stage,
                'po_office' => $case->po_office,
                'created_by' => $user->fname . ' ' . $user->lname
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

protected function createInitialDocumentTracking($case, $user)
{
    // Skip if no PO office is set
    if (empty($case->po_office)) {
        Log::info("No PO office set for case {$case->id}, skipping document tracking creation");
        return;
    }

    // Map PO office to role
    $poOfficeToRole = [
        'Albay' => User::ROLE_PROVINCE_ALBAY,
        'Camarines Sur' => User::ROLE_PROVINCE_CAMARINES_SUR,
        'Camarines Norte' => User::ROLE_PROVINCE_CAMARINES_NORTE,
        'Catanduanes' => User::ROLE_PROVINCE_CATANDUANES,
        'Masbate' => User::ROLE_PROVINCE_MASBATE,
        'Sorsogon' => User::ROLE_PROVINCE_SORSOGON,
    ];

    $initialRole = $poOfficeToRole[$case->po_office] ?? null;

    if (!$initialRole) {
        Log::warning("Could not map PO office '{$case->po_office}' to a role for case {$case->id}");
        return;
    }

    // Check if tracking already exists
    $existingTracking = DocumentTracking::where('case_id', $case->id)->first();
    if ($existingTracking) {
        Log::info("Document tracking already exists for case {$case->id}");
        return;
    }

    // Determine if document should start as "Received" or "Pending"
    // If the creator is from the same province, mark as received
    $isCreatorAtProvince = $user->isProvince() && $user->role === $initialRole;
    
    // Create document tracking
    DocumentTracking::create([
        'case_id' => $case->id,
        'current_role' => $initialRole,
        'status' => $isCreatorAtProvince ? 'Received' : 'Pending Receipt',
        'transferred_by_user_id' => $user->id,
        'transferred_at' => now(),
        'received_by_user_id' => $isCreatorAtProvince ? $user->id : null,
        'received_at' => $isCreatorAtProvince ? now() : null,
        'transfer_notes' => $isCreatorAtProvince 
            ? "Case created by {$user->fname} {$user->lname} at {$case->po_office}" 
            : "Case assigned to {$case->po_office} by {$user->fname} {$user->lname}",
    ]);

    Log::info("Created initial document tracking for case {$case->id} at {$initialRole}", [
        'creator_role' => $user->role,
        'creator_name' => $user->fname . ' ' . $user->lname,
        'is_received' => $isCreatorAtProvince
    ]);
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

    public function moveToNextStage(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $case = CaseFile::findOrFail($id);
            
            // Log the initial state
            Log::info("moveToNextStage called", [
                'case_id' => $id,
                'current_status' => $case->overall_status,
                'current_stage' => $case->current_stage,
                'force_complete' => $request->input('force_complete', false)
            ]);
            
            // Check if request explicitly wants to complete the case (from Complete button)
            $forceComplete = $request->input('force_complete', false);
            
            if ($forceComplete) {
                Log::info("Force completing case {$id}");
                
                // Store old stage for logging
                $oldStage = $case->current_stage;
                
                // Complete case from any stage
                $case->update([
                    'overall_status' => 'Completed',
                ]);
                
                // Verify the update
                $case->refresh();
                Log::info("Case updated", [
                    'case_id' => $case->id,
                    'new_status' => $case->overall_status,
                    'inspection_id' => $case->inspection_id
                ]);
                
                // **ADD THIS: Log the archive action**
                ActivityLogger::logAction(
                    'ARCHIVE',
                    'Case',
                    $case->inspection_id,
                    "Case id: {$case->inspection_id} - {$case->establishment_name} moved to Archived cases",
                    [
                        'establishment' => $case->establishment_name,
                        'previous_stage' => $oldStage,
                        'new_status' => 'Completed',
                        'action_type' => 'Force Complete'
                    ]
                );
                
                DB::commit();
                
                return response()->json([
                    'success' => true,
                    'message' => 'Case marked as complete and moved to archived cases!',
                    'case_id' => $case->id,
                    'new_status' => $case->overall_status
                ]);
            }
            
            // Define stage progression (for normal progression)
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
                $case->update([
                    'overall_status' => 'Completed',
                ]);
                
                // **ADD THIS: Log completion from final stage**
                ActivityLogger::logAction(
                    'ARCHIVE',
                    'Case',
                    $case->inspection_id,
                    "{$case->inspection_id} - {$case->establishment_name} archived from {$currentStage}",
                    [
                        'establishment' => $case->establishment_name,
                        'previous_stage' => $currentStage,
                        'new_status' => 'Completed',
                        'action_type' => 'Normal Progression'
                    ]
                );
                
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
            
            // **ADD THIS: Log normal stage progression**
            ActivityLogger::logAction(
                'UPDATE',
                'Case',
                $case->inspection_id,
                "Stage progression: {$currentStage} â†’ {$nextStage}",
                [
                    'establishment' => $case->establishment_name,
                    'previous_stage' => $currentStage,
                    'new_stage' => $nextStage,
                    'action_type' => 'Stage Progression'
                ]
            );
            
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => "Case moved to {$nextStage} successfully!"
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Failed to move case to next stage: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            // **ADD THIS: Log the error**
            try {
                $case = CaseFile::find($id);
                if ($case) {
                    ActivityLogger::logAction(
                        'ERROR',
                        'Case',
                        $case->inspection_id,
                        "Failed to progress/archive case: " . $e->getMessage(),
                        [
                            'establishment' => $case->establishment_name,
                            'error' => $e->getMessage()
                        ]
                    );
                }
            } catch (\Exception $logError) {
                Log::error('Failed to log error: ' . $logError->getMessage());
            }
            
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
                'establishment_address' => 'nullable|string',  // ADD THIS
                'mode' => 'nullable|string|max:255',           // ADD THIS
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
                
                $changes[] = "$fieldName: '$oldValue' â†’ '$newValue'";
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
    // Validate the uploaded file - now accepts both CSV and Excel
    $validator = Validator::make($request->all(), [
        'csv_file' => 'required|file|mimes:csv,txt,xlsx,xls|max:10240', // 10MB max
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => 'Invalid file. Please upload a CSV or Excel file (max 10MB).',
        ], 422);
    }

    try {
        $file = $request->file('csv_file');
        $extension = $file->getClientOriginalExtension();
        $path = $file->getRealPath();
        
        // If Excel file, convert to CSV
        if (in_array($extension, ['xlsx', 'xls'])) {
            Log::info('Excel file detected, converting to CSV...');
            $path = $this->convertExcelToCsv($file);
        }
        
        // Open and read the CSV file
        $csvData = array_map('str_getcsv', file($path));
        
        // Clean up temporary CSV file if it was converted from Excel
        if (in_array($extension, ['xlsx', 'xls']) && file_exists($path)) {
            unlink($path);
        }
        
        // Get the header row (first row)
        $header = array_map('trim', $csvData[0]);
        
        Log::info('CSV Headers found: ' . count($header) . ' columns');
        
        // Remove ONLY the header row (MIS CSV doesn't have a format row)
        unset($csvData[0]);
        
        $successCount = 0;
        $errors = [];
        $skippedCount = 0;
        
        DB::beginTransaction();
        
        try {
            foreach ($csvData as $index => $row) {
                $rowNumber = $index + 1;
                
                // Skip empty rows
                if (empty(array_filter($row))) {
                    $skippedCount++;
                    continue;
                }
                
                // Ensure row has same number of columns as header
                $headerCount = count($header);
                $rowCount = count($row);
                
                if ($rowCount < $headerCount) {
                    $row = array_pad($row, $headerCount, '');
                } else if ($rowCount > $headerCount) {
                    $row = array_slice($row, 0, $headerCount);
                }
                
                // Map CSV columns to array
                $data = array_combine($header, $row);
                
                // Extract fields from MIS CSV format
                $inspectionId = trim($data['Inspection ID'] ?? '');
                $fieldOffice = trim($data['Field Office'] ?? '');
                $establishmentName = trim($data['Establishment/Ship Name'] ?? '');
                $establishmentAddress = trim($data['Establishment/Ship Address'] ?? '');
                $mode = trim($data['Mode'] ?? '');
                $dateOfInspection = trim($data['Date of Inspection'] ?? '');
                $dateOfNR = trim($data['Date of NR'] ?? '');
                $authorityNo = trim($data['Authority No.'] ?? '');
                $inspector = trim($data['Inspector'] ?? '');
                $caseNo = trim($data['Case No,'] ?? ''); // Note: CSV has comma in header
                $dateScheduled = trim($data['Date Scheduled/Docketed'] ?? '');
                $hearingOfficer = trim($data['Hearing Officer'] ?? '');
                $dispositionStatus = trim($data['Disposition Status'] ?? '');
                $dateSigned = trim($data['Date Signed'] ?? '');
                
                // Check for required fields
                if (empty($inspectionId)) {
                    $errors[] = "Row {$rowNumber}: Missing Inspection ID";
                    continue;
                }
                
                if (empty($establishmentName)) {
                    $errors[] = "Row {$rowNumber}: Missing Establishment/Ship Name";
                    continue;
                }
                
                // Check if inspection_id already exists
                if (CaseFile::where('inspection_id', $inspectionId)->exists()) {
                    $errors[] = "Row {$rowNumber}: Inspection ID '{$inspectionId}' already exists";
                    continue;
                }
                
                // Create case record
                try {
                    CaseFile::create([
                        'inspection_id' => $inspectionId,
                        'po_office' => $fieldOffice,
                        'establishment_name' => $establishmentName,
                        'establishment_address' => $establishmentAddress,
                        'mode' => $mode,
                        'date_of_inspection' => $this->parseDate($dateOfInspection),
                        'date_of_nr' => $this->parseDate($dateOfNR),
                        'inspector_authority_no' => $authorityNo,
                        'inspector_name' => $inspector,
                        'case_no' => $caseNo,
                        'date_scheduled_docketed' => $this->parseDate($dateScheduled),
                        'hearing_officer_mis' => $hearingOfficer,
                        'disposition_mis' => $dispositionStatus,
                        'date_signed_mis' => $this->parseDate($dateSigned),
                        
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
            
            $message = "Successfully imported {$successCount} record(s)";
            if ($skippedCount > 0) {
                $message .= ", skipped {$skippedCount} empty row(s)";
            }
            if (count($errors) > 0) {
                $message .= ", with " . count($errors) . " error(s)";
            }
            
            Log::info("CSV Import completed: {$message}");
            
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
            'message' => 'Error processing file: ' . $e->getMessage()
        ], 500);
    }
}

/**
 * Convert Excel file to CSV
 */
private function convertExcelToCsv($file)
{
    try {
        // Load the Excel file
        $spreadsheet = IOFactory::load($file->getRealPath());
        
        // Get the first worksheet
        $worksheet = $spreadsheet->getActiveSheet();
        
        // Create a temporary CSV file
        $tempCsvPath = storage_path('app/temp_' . time() . '.csv');
        
        // Write to CSV
        $writer = new CsvWriter($spreadsheet);
        $writer->save($tempCsvPath);
        
        Log::info('Excel converted to CSV successfully: ' . $tempCsvPath);
        
        return $tempCsvPath;
        
    } catch (\Exception $e) {
        Log::error('Excel to CSV conversion failed: ' . $e->getMessage());
        throw new \Exception('Failed to convert Excel file: ' . $e->getMessage());
    }
}

/**
 * Helper function to parse dates from CSV
 */
private function parseDate($dateString)
{
    if (empty($dateString)) {
        return null;
    }
    
    // Clean the date string
    $dateString = trim($dateString);
    
    try {
        // Handle Excel date serial numbers
        if (is_numeric($dateString) && $dateString > 0) {
            // Excel stores dates as days since 1900-01-01
            $unixTimestamp = ($dateString - 25569) * 86400;
            return date('Y-m-d', $unixTimestamp);
        }
        
        // Try m/d/Y format (12/3/2003)
        $date = \DateTime::createFromFormat('m/d/Y', $dateString);
        if ($date && $date->format('m/d/Y') === $dateString) {
            return $date->format('Y-m-d');
        }
        
        // Try d/m/Y format
        $date = \DateTime::createFromFormat('d/m/Y', $dateString);
        if ($date && $date->format('d/m/Y') === $dateString) {
            return $date->format('Y-m-d');
        }
        
        // Try Y-m-d format
        $date = \DateTime::createFromFormat('Y-m-d', $dateString);
        if ($date && $date->format('Y-m-d') === $dateString) {
            return $date->format('Y-m-d');
        }
        
        // Fallback to strtotime
        $timestamp = strtotime($dateString);
        if ($timestamp !== false) {
            return date('Y-m-d', $timestamp);
        }
        
        return null;
    } catch (\Exception $e) {
        Log::warning('Date parse error for: ' . $dateString . ' - ' . $e->getMessage());
        return null;
    }
}
/**
 * Get document checklist for a case
 */
public function getDocuments($id)
{
    try {
        $case = CaseFile::findOrFail($id);
        
        return response()->json([
            'success' => true,
            'documents' => $case->document_checklist ?? []
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Failed to load documents'
        ], 500);
    }
}

/**
 * Save document checklist for a case
 */
public function saveDocuments(Request $request, $id)
{
    DB::beginTransaction();
    try {
        $case = CaseFile::findOrFail($id);
        
        // Get documents and ensure checked is boolean
        $documents = $request->input('documents', []);
        
        // Convert 'checked' from string to boolean
        $documents = array_map(function($doc) {
            if (isset($doc['checked'])) {
                $doc['checked'] = filter_var($doc['checked'], FILTER_VALIDATE_BOOLEAN);
            }
            return $doc;
        }, $documents);
        
        $case->update([
            'document_checklist' => $documents
        ]);

        ActivityLogger::logAction(
            'UPDATE',
            'Case',
            $case->inspection_id,
            "Updated document checklist",
            ['establishment' => $case->establishment_name]
        );

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Documents saved successfully'
        ]);
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Error saving documents: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Failed to save documents'
        ], 500);
    }
}

/**
 * Upload file for a document in checklist
 */
public function uploadDocumentFile(Request $request, $caseId, $documentId)
{
    $request->validate([
        'file' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png,xlsx,xls|max:10240', // 10MB max
    ]);

    DB::beginTransaction();
    try {
        $case = CaseFile::findOrFail($caseId);
        $documents = $case->document_checklist ?? [];
        
        // Convert documentId to integer for comparison
        $documentId = (int) $documentId;
        
        // Find the document in the checklist
        $documentIndex = null;
        foreach ($documents as $index => $doc) {
            if (isset($doc['id']) && (int)$doc['id'] == $documentId) {
                $documentIndex = $index;
                break;
            }
        }
        
        if ($documentIndex === null) {
            return response()->json([
                'success' => false,
                'message' => 'Document not found in checklist'
            ], 404);
        }
        
        // IMPORTANT: Create directory if it doesn't exist
        $uploadDir = storage_path("app/case_documents/{$caseId}");
        if (!file_exists($uploadDir)) {
            if (!mkdir($uploadDir, 0777, true) && !is_dir($uploadDir)) {
                throw new \Exception("Failed to create upload directory: {$uploadDir}");
            }
            Log::info("Created directory: {$uploadDir}");
        }
        
        // Handle file upload
        $file = $request->file('file');
        
        // Get file info IMMEDIATELY before it's deleted
        $originalName = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $fileSize = $file->getSize(); // Get size NOW
        
        // Create unique filename: {document_id}_{timestamp}.{ext}
        $filename = $documentId . '_' . time() . '.' . $extension;
        
        // Use move() instead of storeAs() for better Windows compatibility
        $destinationPath = $uploadDir . '/' . $filename;
        
        try {
            // Try Laravel's storeAs first
            $path = $file->storeAs(
                "case_documents/{$caseId}",
                $filename,
                'local'
            );
            
            $fullPath = storage_path('app/' . $path);
            
            // Verify file was actually saved
            if (!file_exists($fullPath)) {
                // Fallback: try manual move
                Log::warning("storeAs didn't work, trying manual move");
                
                if (!$file->move($uploadDir, $filename)) {
                    throw new \Exception("Both storeAs and move failed");
                }
                
                $path = "case_documents/{$caseId}/{$filename}";
                $fullPath = $destinationPath;
            }
            
            // Final verification
            if (!file_exists($fullPath)) {
                throw new \Exception("File upload failed - file not found after storage: {$fullPath}");
            }
            
            Log::info('File successfully uploaded', [
                'path' => $path,
                'full_path' => $fullPath,
                'file_exists' => file_exists($fullPath),
                'file_size' => filesize($fullPath)
            ]);
            
        } catch (\Exception $e) {
            Log::error('Upload error details', [
                'error' => $e->getMessage(),
                'upload_dir' => $uploadDir,
                'dir_exists' => file_exists($uploadDir),
                'dir_writable' => is_writable($uploadDir),
                'tmp_file' => $file->getRealPath(),
                'tmp_exists' => file_exists($file->getRealPath())
            ]);
            throw $e;
        }
        
        // Log the storage path
        Log::info('File uploaded', [
            'case_id' => $caseId,
            'document_id' => $documentId,
            'filename' => $filename,
            'stored_path' => $path,
            'full_path' => storage_path('app/' . $path),
            'file_exists' => file_exists(storage_path('app/' . $path))
        ]);
        
        // Delete old file if exists
        if (isset($documents[$documentIndex]['file_path']) && !empty($documents[$documentIndex]['file_path'])) {
            Storage::disk('local')->delete($documents[$documentIndex]['file_path']);
        }
        
        // Update document with file info
        $documents[$documentIndex]['file_path'] = $path;  // This should be: "case_documents/{case_id}/{filename}"
        $documents[$documentIndex]['file_name'] = $originalName;
        $documents[$documentIndex]['file_size'] = $fileSize; // Use the size we got earlier
        $documents[$documentIndex]['uploaded_at'] = now()->toDateTimeString();
        $documents[$documentIndex]['uploaded_by'] = Auth::user()->fname . ' ' . Auth::user()->lname;
        
        // Re-index array to maintain proper structure
        $documents = array_values($documents);
        
        // Save updated checklist
        $case->update([
            'document_checklist' => $documents
        ]);
        
        ActivityLogger::logAction(
            'UPLOAD',
            'Case Document',
            $case->inspection_id,
            "Uploaded file '{$originalName}' for document '{$documents[$documentIndex]['title']}'",
            [
                'establishment' => $case->establishment_name,
                'file_name' => $originalName,
                'file_size' => $fileSize // Use stored size
            ]
        );
        
        DB::commit();
        
        return response()->json([
            'success' => true,
            'message' => 'File uploaded successfully',
            'file_name' => $originalName,
            'file_size' => $this->formatBytes($fileSize), // Use stored size
            'uploaded_at' => now()->format('M d, Y h:i A')
        ]);
        
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Error uploading document file: ' . $e->getMessage());
        Log::error('Stack trace: ' . $e->getTraceAsString());
        
        return response()->json([
            'success' => false,
            'message' => 'Failed to upload file: ' . $e->getMessage()
        ], 500);
    }
}

/**
 * Download/view uploaded document file
 */
public function downloadDocumentFile($caseId, $documentId)
{
    try {
        $case = CaseFile::findOrFail($caseId);
        $documents = $case->document_checklist ?? [];
        
        // Convert documentId to integer for comparison
        $documentId = (int) $documentId;
        
        // Find the document - use loose comparison
        $document = null;
        foreach ($documents as $doc) {
            if (isset($doc['id']) && (int)$doc['id'] == $documentId) {
                $document = $doc;
                break;
            }
        }
        
        Log::info('Download attempt', [
            'case_id' => $caseId,
            'document_id' => $documentId,
            'found_document' => $document !== null,
            'has_file_path' => isset($document['file_path']),
            'all_documents' => $documents
        ]);
        
        if (!$document || !isset($document['file_path'])) {
            Log::error('File not found in document array', [
                'document' => $document,
                'all_documents' => $documents
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'File not found in document checklist'
            ], 404);
        }
        
        $filePath = storage_path('app/' . $document['file_path']);
        
        Log::info('Attempting to download file', [
            'full_path' => $filePath,
            'exists' => file_exists($filePath)
        ]);
        
        if (!file_exists($filePath)) {
            Log::error('File not found on server', [
                'path' => $filePath,
                'document_file_path' => $document['file_path']
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'File not found on server: ' . basename($filePath)
            ], 404);
        }
        
        ActivityLogger::logAction(
            'VIEW',
            'Case Document',
            $case->inspection_id,
            "Viewed file '{$document['file_name']}' for document '{$document['title']}'",
            ['establishment' => $case->establishment_name]
        );
        
        // Get file extension to determine if viewable
        $extension = strtolower(pathinfo($document['file_name'], PATHINFO_EXTENSION));
        $viewableExtensions = ['pdf', 'jpg', 'jpeg', 'png', 'gif', 'bmp', 'svg', 'txt'];
        
        // Determine MIME type
        $mimeTypes = [
            'pdf' => 'application/pdf',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'bmp' => 'image/bmp',
            'svg' => 'image/svg+xml',
            'txt' => 'text/plain',
        ];
        
        $mimeType = $mimeTypes[$extension] ?? 'application/octet-stream';
        
        // If viewable, display inline; otherwise download
        if (in_array($extension, $viewableExtensions)) {
            return response()->file($filePath, [
                'Content-Type' => $mimeType,
                'Content-Disposition' => 'inline; filename="' . $document['file_name'] . '"'
            ]);
        } else {
            // Force download for non-viewable files (doc, docx, xlsx, etc.)
            return response()->download($filePath, $document['file_name']);
        }
        
    } catch (\Exception $e) {
        Log::error('Error downloading document file: ' . $e->getMessage());
        Log::error('Stack trace: ' . $e->getTraceAsString());
        
        return response()->json([
            'success' => false,
            'message' => 'Failed to download file: ' . $e->getMessage()
        ], 500);
    }
}

/**
 * Delete uploaded document file - FIXED VERSION
 */
public function deleteDocumentFile($caseId, $documentId)
{
    DB::beginTransaction();
    try {
        $case = CaseFile::findOrFail($caseId);
        $documents = $case->document_checklist ?? [];
        
        // Convert documentId to integer for comparison
        $documentId = (int) $documentId;
        
        // Find the document index - use loose comparison
        $documentIndex = null;
        foreach ($documents as $index => $doc) {
            if (isset($doc['id']) && (int)$doc['id'] == $documentId) {
                $documentIndex = $index;
                break;
            }
        }
        
        if ($documentIndex === null || !isset($documents[$documentIndex]['file_path'])) {
            return response()->json([
                'success' => false,
                'message' => 'File not found'
            ], 404);
        }
        
        $fileName = $documents[$documentIndex]['file_name'] ?? 'Unknown';
        
        // Delete physical file
        Storage::disk('local')->delete($documents[$documentIndex]['file_path']);
        
        // Remove file info from document
        unset($documents[$documentIndex]['file_path']);
        unset($documents[$documentIndex]['file_name']);
        unset($documents[$documentIndex]['file_size']);
        unset($documents[$documentIndex]['uploaded_at']);
        unset($documents[$documentIndex]['uploaded_by']);
        
        // Re-index array to maintain proper structure
        $documents = array_values($documents);
        
        // Save updated checklist
        $case->update([
            'document_checklist' => $documents
        ]);
        
        ActivityLogger::logAction(
            'DELETE',
            'Case Document',
            $case->inspection_id,
            "Deleted file '{$fileName}' from document '{$documents[$documentIndex]['title']}'",
            ['establishment' => $case->establishment_name]
        );
        
        DB::commit();
        
        return response()->json([
            'success' => true,
            'message' => 'File deleted successfully'
        ]);
        
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Error deleting document file: ' . $e->getMessage());
        Log::error('Stack trace: ' . $e->getTraceAsString());
        
        return response()->json([
            'success' => false,
            'message' => 'Failed to delete file'
        ], 500);
    }
}

/**
 * Helper function to format file size
 */
private function formatBytes($bytes, $precision = 2)
{
    $units = ['B', 'KB', 'MB', 'GB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);
    
    return round($bytes, $precision) . ' ' . $units[$pow];
}

protected function isProvinceUser($role)
{
    return in_array($role, User::PROVINCE_ROLES);
}

protected function getProvinceNameFromRole($role)
{
    $roleToProvince = [
        User::ROLE_PROVINCE_ALBAY => 'Albay',
        User::ROLE_PROVINCE_CAMARINES_SUR => 'Camarines Sur',
        User::ROLE_PROVINCE_CAMARINES_NORTE => 'Camarines Norte',
        User::ROLE_PROVINCE_CATANDUANES => 'Catanduanes',
        User::ROLE_PROVINCE_MASBATE => 'Masbate',
        User::ROLE_PROVINCE_SORSOGON => 'Sorsogon',
    ];
    
    return $roleToProvince[$role] ?? null;
}

}