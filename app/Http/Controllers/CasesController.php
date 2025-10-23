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

        try {
            $case = CaseFile::create($validated);

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

            // Create related inspection record if starting at inspections stage
            if ($case->current_stage === '1: Inspections') {
                Inspection::create(['case_id' => $case->id]);
            }

            return redirect()->route('case.index')->with('success', 'Case created successfully!');
        } catch (\Exception $e) {
            Log::error('Error creating case: ' . $e->getMessage());
            return redirect()->route('case.index')->with('error', 'Failed to create case: ' . $e->getMessage());
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

        try {
            $case = CaseFile::findOrFail($id);
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

            return redirect()->route('case.index')->with('success', 'Case updated successfully!');
        } catch (\Exception $e) {
            Log::error('Error updating case: ' . $e->getMessage());
            return redirect()->route('case.index')->with('error', 'Failed to update case: ' . $e->getMessage());
        }
    }

    /**
     * Delete case
     */
    public function destroy($id)
    {
        try {
            $case = CaseFile::findOrFail($id);
            $caseInfo = $case->inspection_id . ' - ' . $case->establishment_name;
            
            $case->delete();
            
            ActivityLogger::logAction(
                'DELETE',
                'Case',
                $caseInfo,
                'Case deleted',
                ['establishment' => $case->establishment_name]
            );

            return response()->json([
                'success' => true,
                'message' => 'Case deleted successfully.'
            ]);
        } catch (\Exception $e) {
            Log::error('Case delete failed: ' . $e->getMessage());
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
        try {
            $case = CaseFile::findOrFail($id);
            $oldStage = $case->current_stage;
            $isCompletion = false;
            $newStage = null;

            switch ($case->current_stage) {
                case '1: Inspections':
                    Docketing::create(['case_id' => $case->id]);
                    $newStage = '2: Docketing';
                    $case->update(['current_stage' => $newStage]);
                    break;
                
                case '2: Docketing':
                    HearingProcess::create(['case_id' => $case->id]);
                    $newStage = '3: Hearing';
                    $case->update(['current_stage' => $newStage]);
                    break;
                
                case '3: Hearing':
                    ReviewAndDrafting::create(['case_id' => $case->id]);
                    $newStage = '4: Review & Drafting';
                    $case->update(['current_stage' => $newStage]);
                    break;
                
                case '4: Review & Drafting':
                    OrderAndDisposition::create(['case_id' => $case->id]);
                    $newStage = '5: Orders & Disposition';
                    $case->update(['current_stage' => $newStage]);
                    break;
                
                case '5: Orders & Disposition':
                    ComplianceAndAward::create(['case_id' => $case->id]);
                    $newStage = '6: Compliance & Awards';
                    $case->update(['current_stage' => $newStage]);
                    break;
                
                case '6: Compliance & Awards':
                    AppealsAndResolution::create(['case_id' => $case->id]);
                    $newStage = '7: Appeals & Resolution';
                    $case->update(['current_stage' => $newStage]);
                    break;
                
                case '7: Appeals & Resolution':
                    // Final stage - mark case as completed
                    $case->update(['overall_status' => 'Completed']);
                    $isCompletion = true;
                    break;
                
                default:
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid current stage.'
                    ], 400);
            }

            // Log the action
            if ($isCompletion) {
                ActivityLogger::logAction(
                    'COMPLETE',
                    'Case',
                    $case->inspection_id,
                    'Case marked as completed in Appeals & Resolution',
                    [
                        'establishment' => $case->establishment_name,
                        'final_stage' => '7: Appeals & Resolution',
                        'status' => 'Moved to Archived Cases'
                    ]
                );
                
                $message = 'Case completed successfully and moved to archived cases!';
            } else {
                $oldStageName = explode(': ', $oldStage)[1] ?? $oldStage;
                $newStageName = explode(': ', $newStage)[1] ?? $newStage;
                
                ActivityLogger::logAction(
                    'PROGRESS',
                    'Case',
                    $case->inspection_id,
                    "Moved from $oldStageName to $newStageName",
                    [
                        'establishment' => $case->establishment_name,
                        'from_stage' => $oldStageName,
                        'to_stage' => $newStageName
                    ]
                );
                
                $message = "Case moved from $oldStageName to $newStageName successfully!";
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $case
            ]);

        } catch (\Exception $e) {
            Log::error('Error moving case to next stage: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to move case to next stage.'
            ], 500);
        }
    }

    /**
     * Inline update via AJAX
     */
    public function inlineUpdate(Request $request, $id)
    {
        try {
            $case = CaseFile::findOrFail($id);
            $oldData = $case->toArray();
            
            // Only allow specific fields to be updated inline
            $allowedFields = ['inspection_id', 'case_no', 'establishment_name', 'current_stage', 'overall_status'];
            $updateData = $request->only($allowedFields);
            
            if (empty($updateData)) {
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

            return response()->json([
                'success' => true,
                'message' => 'Case updated successfully!',
                'data' => $case
            ]);
        } catch (\Exception $e) {
            Log::error('Case inline update failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update case.'
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
}