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
            'malsu' => [7],                          // MALSU sees Appeals
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
                'error' => 'You do not have access to this tab.',
                'success' => false
            ], 403);
        }

        try {
            $data = null;
            $html = '';

            switch ($tabNumber) {
                case '1': // Inspections
                    $data = Inspection::with(['case' => function($query) {
                        $query->select('id', 'inspection_id', 'case_no', 'establishment_name', 'current_stage', 'overall_status');
                    }])
                    ->whereHas('case', function($query) {
                        $query->where('current_stage', '1: Inspections')
                            ->where('overall_status', '!=', 'Completed');
                    })
                    ->get();

                    $html = view('frontend.partials.inspection_table', ['inspections' => $data])->render();
                    break;

                case '2': // Docketing
                    $data = Docketing::with(['case' => function($query) {
                        $query->select('id', 'inspection_id', 'case_no', 'establishment_name', 'current_stage', 'overall_status');
                    }])
                    ->whereHas('case', function($query) {
                        $query->where('current_stage', '2: Docketing')
                            ->where('overall_status', '!=', 'Completed');
                    })
                    ->get();

                    $html = view('frontend.partials.docketing_table', ['docketing' => $data])->render();
                    break;

                case '3': // Hearing
                    $data = HearingProcess::with(['case' => function($query) {
                        $query->select('id', 'inspection_id', 'case_no', 'establishment_name', 'current_stage', 'overall_status');
                    }])
                    ->whereHas('case', function($query) {
                        $query->where('current_stage', '3: Hearing')
                            ->where('overall_status', '!=', 'Completed');
                    })
                    ->get();

                    $html = view('frontend.partials.hearing_table', ['hearingProcess' => $data])->render();
                    break;

                case '4': // Review & Drafting
                    $data = ReviewAndDrafting::with(['case' => function($query) {
                        $query->select('id', 'inspection_id', 'case_no', 'establishment_name', 'current_stage', 'overall_status');
                    }])
                    ->whereHas('case', function($query) {
                        $query->where('current_stage', '4: Review & Drafting')
                            ->where('overall_status', '!=', 'Completed');
                    })
                    ->get();

                    $html = view('frontend.partials.review_table', ['reviewAndDrafting' => $data])->render();
                    break;

                case '5': // Orders & Disposition
                    $data = OrderAndDisposition::with(['case' => function($query) {
                        $query->select('id', 'inspection_id', 'case_no', 'establishment_name', 'current_stage', 'overall_status');
                    }])
                    ->whereHas('case', function($query) {
                        $query->where('current_stage', '5: Orders & Disposition')
                            ->where('overall_status', '!=', 'Completed');
                    })
                    ->get();

                    $html = view('frontend.partials.orders_table', ['ordersAndDisposition' => $data])->render();
                    break;

                case '6': // Compliance & Awards
                    $data = ComplianceAndAward::with(['case' => function($query) {
                        $query->select('id', 'inspection_id', 'case_no', 'establishment_name', 'current_stage', 'overall_status');
                    }])
                    ->whereHas('case', function($query) {
                        $query->where('current_stage', '6: Compliance & Awards')
                            ->where('overall_status', '!=', 'Completed');
                    })
                    ->get();

                    $html = view('frontend.partials.compliance_table', ['complianceAndAwards' => $data])->render();
                    break;

                case '7': // Appeals & Resolution
                    $data = AppealsAndResolution::with(['case' => function($query) {
                        $query->select('id', 'inspection_id',  'case_no','establishment_name', 'current_stage', 'overall_status');
                    }])
                    ->whereHas('case', function($query) {
                        $query->where('current_stage', '7: Appeals & Resolution')
                            ->where('overall_status', '!=', 'Completed');
                    })
                    ->get();

                    $html = view('frontend.partials.appeals_table', ['appealsAndResolutions' => $data])->render();
                    break;

                default:
                    return response()->json(['error' => 'Invalid tab number'], 400);
            }

            return response()->json([
                'success' => true,
                'html' => $html,
                'count' => $data ? $data->count() : 0
            ]);
        } catch (\Exception $e) {
            Log::error('Error loading tab data: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to load data.'], 500);
        }
    }

    /**
     * Store new case
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'inspection_id' => 'required|string|max:255',
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

            if ($case->current_stage === '1: Inspections') {
                Inspection::create(['case_id' => $case->id]);
            }

            return redirect()->route('case.index')->with('success', 'Case created successfully!');
        } catch (\Exception $e) {
            Log::error('Error creating case: ' . $e->getMessage());
            return redirect()->route('case.index')->with('error', 'Failed to create case.');
        }
    }

    /**
     * Update case
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'inspection_id' => 'required|string|max:255',
            'case_no' => 'nullable|string|max:255',
            'establishment_name' => 'required|string|max:255',
            'current_stage' => 'required|in:1: Inspections,2: Docketing,3: Hearing,4: Review & Drafting,5: Orders & Disposition,6: Compliance & Awards,7: Appeals & Resolution',
            'overall_status' => 'required|in:Active,Completed,Dismissed',
        ]);

        try {
            $case = CaseFile::findOrFail($id);
            $oldData = $case->toArray();
            
            $case->update($validated);

            $changes = [];
            foreach ($validated as $key => $value) {
                if (isset($oldData[$key]) && $oldData[$key] != $value) {
                    $fieldName = ucfirst(str_replace('_', ' ', $key));
                    $oldValue = $oldData[$key] ?: '(empty)';
                    $newValue = $value ?: '(empty)';
                    
                    if ($key === 'current_stage') {
                        $oldValue = explode(': ', $oldValue)[1] ?? $oldValue;
                        $newValue = explode(': ', $newValue)[1] ?? $newValue;
                    }
                    
                    $changes[] = "{$fieldName}: '{$oldValue}' → '{$newValue}'";
                }
            }

            $changeDescription = !empty($changes) 
                ? implode(', ', $changes)
                : 'No changes detected';

            ActivityLogger::logAction(
                'UPDATE',
                'Case',
                $case->inspection_id,
                "Updated: {$changeDescription}",
                ['establishment' => $case->establishment_name]
            );

            return redirect()->route('case.index')->with('success', 'Case updated successfully!');
        } catch (\Exception $e) {
            Log::error('Error updating case: ' . $e->getMessage());
            return redirect()->route('case.index')->with('error', 'Failed to update case.');
        }
    }

    /**
     * Delete case
     */
    public function destroy($id)
    {
        try {
            $case = CaseFile::findOrFail($id);
            $case->delete();
            ActivityLogger::log('Deleted case', ['case_id' => $id]);
            return response()->json(['success' => true, 'message' => 'Case deleted successfully.']);
        } catch (\Exception $e) {
            Log::error('Case delete failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to delete case.'], 500);
        }
    }

    /**
     * Show case
     */
    public function show($id)
    {
        $case = CaseFile::findOrFail($id);
        return response()->json($case);
    }

    /**
     * Edit case
     */
    public function edit($id)
    {
        $case = CaseFile::findOrFail($id);
        ActivityLogger::log('Opened case edit form', ['case_id' => $id]);
        return response()->json($case);
    }

    /**
     * Move case to next stage
     */
public function moveToNextStage(Request $request, $id)
{
    try {
        $case = CaseFile::findOrFail($id);
        $oldStage = $case->current_stage;
        $isCompletion = false;

        switch ($case->current_stage) {
            case '1: Inspections':
                Docketing::create(['case_id' => $case->id]);
                $case->update(['current_stage' => '2: Docketing']);
                break;
            case '2: Docketing':
                HearingProcess::create(['case_id' => $case->id]);
                $case->update(['current_stage' => '3: Hearing']);
                break;
            case '3: Hearing':
                ReviewAndDrafting::create(['case_id' => $case->id]);
                $case->update(['current_stage' => '4: Review & Drafting']);
                break;
            case '4: Review & Drafting':
                OrderAndDisposition::create(['case_id' => $case->id]);
                $case->update(['current_stage' => '5: Orders & Disposition']);
                break;
            case '5: Orders & Disposition':
                ComplianceAndAward::create(['case_id' => $case->id]);
                $case->update(['current_stage' => '6: Compliance & Awards']);
                break;
            case '6: Compliance & Awards':
                AppealsAndResolution::create(['case_id' => $case->id]);
                $case->update(['current_stage' => '7: Appeals & Resolution']);
                break;
            case '7: Appeals & Resolution':
                // This is the final stage - mark case as completed
                $case->update(['overall_status' => 'Completed']);
                $isCompletion = true;
                break;
            default:
                return redirect()->back()->with('error', 'Invalid current stage');
        }

        // Log the action with different messages based on whether it's completion or stage progression
        if ($isCompletion) {
            ActivityLogger::logAction(
                'COMPLETE',
                'Case',
                $case->inspection_id,
                'Case marked as completed in Appeals & Resolution',
                [
                    'establishment' => $case->establishment_name,
                    'status' => 'Moved to Archived Cases'
                ]
            );
            
            $successMessage = 'Case completed successfully and moved to archived cases!';
        } else {
            ActivityLogger::logAction(
                'PROGRESS',
                'Case',
                $case->inspection_id,
                "Moved to next stage",
                [
                    'establishment' => $case->establishment_name,
                    'from_stage' => explode(': ', $oldStage)[1] ?? $oldStage,
                    'to_stage' => explode(': ', $case->current_stage)[1] ?? $case->current_stage
                ]
            );
            
            $successMessage = 'Case moved to next stage successfully.';
        }

        return redirect()->back()->with('success', $successMessage);
    } catch (\Exception $e) {
        Log::error('Error moving case to next stage: ' . $e->getMessage());
        return redirect()->back()->with('error', 'Failed to move case to next stage.');
    }
}
    /**
     * Inline update (AJAX)
     */
    public function inlineUpdate(Request $request, $id)
    {
        try {
            $case = CaseFile::findOrFail($id);
            $oldData = $case->toArray();
            
            $case->update($request->all());
            $case->refresh();

            $changes = [];
            foreach ($request->all() as $key => $value) {
                if (isset($oldData[$key]) && $oldData[$key] != $value) {
                    $fieldName = ucfirst(str_replace('_', ' ', $key));
                    $oldValue = $oldData[$key] ?: '(empty)';
                    $newValue = $value ?: '(empty)';
                    
                    if ($key === 'current_stage') {
                        $oldValue = explode(': ', $oldValue)[1] ?? $oldValue;
                        $newValue = explode(': ', $newValue)[1] ?? $newValue;
                    }
                    
                    $changes[] = "{$fieldName}: '{$oldValue}' → '{$newValue}'";
                }
            }

            $changeDescription = !empty($changes) 
                ? implode(', ', $changes)
                : 'No changes detected';

            ActivityLogger::logAction(
                'UPDATE',
                'Case',
                $case->inspection_id,
                "Inline updated: {$changeDescription}",
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
}