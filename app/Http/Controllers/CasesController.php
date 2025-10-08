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

use Illuminate\Support\Facades\Log;

class CasesController extends Controller
{
    /**
     * Display the main case management page
     * OPTIMIZED: Only loads Tab 0 data initially
     */
    public function case()
    {
        // ONLY load Tab 0 (All Active Cases) on initial page load
        $cases = CaseFile::select('id', 'inspection_id', 'case_no', 'establishment_name', 'current_stage', 'overall_status', 'created_at')
            ->where('overall_status', '!=', 'Completed')
            ->orderBy('created_at', 'desc')
            ->get();

        // Pass empty collections for other tabs - they'll be loaded via AJAX
        return view('frontend.case', [
            'cases' => $cases,
            'inspections' => collect([]), // Empty collection
            'docketing' => collect([]),
            'hearingProcess' => collect([]),
            'reviewAndDrafting' => collect([]),
            'ordersAndDisposition' => collect([]),
            'complianceAndAwards' => collect([]),
            'appealsAndResolutions' => collect([])
        ]);
    }

    /**
     * NEW METHOD: Load tab data via AJAX
     */
    public function loadTabData(Request $request, $tabNumber)
    {
        try {
            $data = null;
            $html = '';

            switch ($tabNumber) {
                case '1': // Inspections
                    $data = Inspection::with(['case' => function($query) {
                            $query->select('id', 'inspection_id', 'establishment_name', 'current_stage', 'overall_status');
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
                            $query->select('id', 'inspection_id', 'establishment_name', 'current_stage', 'overall_status');
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
                            $query->select('id', 'inspection_id', 'establishment_name', 'current_stage', 'overall_status');
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
                            $query->select('id', 'inspection_id', 'establishment_name', 'current_stage', 'overall_status');
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
                            $query->select('id', 'inspection_id', 'establishment_name', 'current_stage', 'overall_status');
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
                            $query->select('id', 'inspection_id', 'establishment_name', 'current_stage', 'overall_status');
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
                            $query->select('id', 'inspection_id', 'establishment_name', 'current_stage', 'overall_status');
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
            return response()->json([
                'error' => 'Failed to load data: ' . $e->getMessage()
            ], 500);
        }
    }

    // ... rest of your existing methods (store, update, destroy, etc.) remain unchanged ...

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
            
            if ($case->current_stage === '1: Inspections') {
                Inspection::create([
                    'case_id' => $case->id,
                    'po_office' => null,
                    'inspector_name' => null,
                    'inspector_authority_no' => null,
                    'date_of_inspection' => null,
                    'date_of_nr' => null,
                    'twg_ali' => null,
                ]);
            }
            
            return redirect()->route('case.index')->with('success', 'Case created successfully!');
            
        } catch (\Exception $e) {
            Log::error('Error creating case: ' . $e->getMessage());
            return redirect()->route('case.index')->with('error', 'Failed to create case: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'inspection_id' => 'required|string|max:255',
            'case_no' => 'nullable|string|max:255',
            'establishment_name' => 'required|string|max:255',
            'current_stage' => 'required|in:1: Inspections,2: Docketing,3: Hearing,4: Review & Drafting,5: Orders & Disposition,6: Compliance & Awards,7: Appeals & Resolution',
            'overall_status' => 'required|in:Active,Completed,Dismissed',
        ]);

        $case = CaseFile::findOrFail($id);
        $case->update($validated);
        
        return redirect()->route('case.index')->with('success', 'Case updated successfully!');
    }

    public function destroy($id)
    {
        Log::info("Delete request received for case ID: " . $id);
        
        try {
            $case = CaseFile::find($id);
            
            if (!$case) {
                if (request()->expectsJson()) {
                    return response()->json(['error' => 'Case not found'], 404);
                }
                return redirect()->route('case.index')->with('error', 'Case not found');
            }
            
            $case->delete();
            
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Case deleted successfully!'
                ]);
            }
            
            return redirect()->route('case.index')->with('success', 'Case deleted successfully!');
            
        } catch (\Exception $e) {
            Log::error('Delete error: ' . $e->getMessage());
            
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete case: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->route('case.index')->with('error', 'Failed to delete case.');
        }
    }

    public function show($id)
    {
        $case = CaseFile::findOrFail($id);
        return response()->json($case);
    }

    public function edit($id)
    {
        $case = CaseFile::findOrFail($id);
        return response()->json($case);
    }

    public function moveToNextStage(Request $request, $id)
    {
        try {
            $case = CaseFile::findOrFail($id);
            
            switch ($case->current_stage) {
                case '1: Inspections':
                    Docketing::create(['case_id' => $case->id]);
                    $case->update(['current_stage' => '2: Docketing']);
                    return redirect()->back()
                        ->with('success', 'Case successfully moved to Docketing stage')
                        ->with('active_tab', 'tab2');
                    
                case '2: Docketing':
                    HearingProcess::create(['case_id' => $case->id]);
                    $case->update(['current_stage' => '3: Hearing']);
                    return redirect()->back()
                        ->with('success', 'Case successfully moved to Hearing stage')
                        ->with('active_tab', 'tab3');
                    
                case '3: Hearing':
                    ReviewAndDrafting::create(['case_id' => $case->id]);
                    $case->update(['current_stage' => '4: Review & Drafting']);
                    return redirect()->back()
                        ->with('success', 'Case successfully moved to Review & Drafting stage')
                        ->with('active_tab', 'tab4');
                    
                case '4: Review & Drafting':
                    OrderAndDisposition::create(['case_id' => $case->id]);
                    $case->update(['current_stage' => '5: Orders & Disposition']);
                    return redirect()->back()
                        ->with('success', 'Case successfully moved to Orders & Disposition stage')
                        ->with('active_tab', 'tab5');
                    
                case '5: Orders & Disposition':
                    ComplianceAndAward::create(['case_id' => $case->id]);
                    $case->update(['current_stage' => '6: Compliance & Awards']);
                    return redirect()->back()
                        ->with('success', 'Case successfully moved to Compliance & Awards stage')
                        ->with('active_tab', 'tab6');
                    
                case '6: Compliance & Awards':
                    AppealsAndResolution::create(['case_id' => $case->id]);
                    $case->update(['current_stage' => '7: Appeals & Resolution']);
                    return redirect()->back()
                        ->with('success', 'Case successfully moved to Appeals & Resolution stage')
                        ->with('active_tab', 'tab7');
                    
                case '7: Appeals & Resolution':
                    $case->update(['overall_status' => 'Completed']);
                    return redirect()->back()->with('success', 'Case has been completed');
                    
                default:
                    return redirect()->back()->with('error', 'Invalid current stage');
            }
            
        } catch (\Exception $e) {
            Log::error('Error moving case to next stage: ' . $e->getMessage(), [
                'case_id' => $id,
                'current_stage' => $case->current_stage ?? 'unknown',
                'stack_trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()->with('error', 'Failed to move case to next stage: ' . $e->getMessage());
        }
    }

    public function inlineUpdate(Request $request, $id)
    {
        Log::info('Case inline update request received', [
            'case_id' => $id,
            'request_data' => $request->all(),
        ]);
        
        try {
            $case = CaseFile::findOrFail($id);
            
            $inputData = $request->all();
            
            $cleanedData = [];
            foreach ($inputData as $key => $value) {
                $cleanedData[$key] = ($value === '' || $value === '-') ? null : $value;
            }
            
            $validator = \Illuminate\Support\Facades\Validator::make($cleanedData, [
                'inspection_id' => 'nullable|string|max:255',
                'case_no' => 'nullable|string|max:255',
                'establishment_name' => 'nullable|string|max:500',
                'current_stage' => 'nullable|in:1: Inspections,2: Docketing,3: Hearing,4: Review & Drafting,5: Orders & Disposition,6: Compliance & Awards,7: Appeals & Resolution',
                'overall_status' => 'nullable|in:Active,Completed,Dismissed',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed: ' . $validator->errors()->first(),
                    'errors' => $validator->errors()
                ], 422);
            }

            $case->update($validator->validated());
            $case->refresh();

            $responseData = [
                'inspection_id' => $case->inspection_id ?? '-',
                'case_no' => $case->case_no ?? '-',
                'establishment_name' => $case->establishment_name ?? '-',
                'current_stage' => $case->current_stage ?? '-',
                'overall_status' => $case->overall_status ?? '-',
                'created_at' => $case->created_at ? \Carbon\Carbon::parse($case->created_at)->format('Y-m-d') : '-',
            ];

            return response()->json([
                'success' => true,
                'message' => 'Case updated successfully!',
                'data' => $responseData
            ]);

        } catch (\Exception $e) {
            Log::error('Case inline update failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to update case: ' . $e->getMessage()
            ], 500);
        }
    }
}