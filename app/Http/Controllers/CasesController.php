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
    public function case()
    {
        // Filter out completed cases - only show Active and Dismissed
        $cases = CaseFile::where('overall_status', '!=', 'Completed')->get();
        
        // Filter each stage by the related case's current_stage AND exclude completed cases
        $inspections = Inspection::with('case')
            ->whereHas('case', function($query) {
                $query->where('current_stage', '1: Inspections')
                    ->where('overall_status', '!=', 'Completed');
            })
            ->get();
            
        $docketing = Docketing::with('case')
            ->whereHas('case', function($query) {
                $query->where('current_stage', '2: Docketing')
                    ->where('overall_status', '!=', 'Completed');
            })
            ->get();
            
        $hearingProcess = HearingProcess::with('case')
            ->whereHas('case', function($query) {
                $query->where('current_stage', '3: Hearing')
                    ->where('overall_status', '!=', 'Completed');
            })
            ->get();
            
        $reviewAndDrafting = ReviewAndDrafting::with('case')
            ->whereHas('case', function($query) {
                $query->where('current_stage', '4: Review & Drafting')
                    ->where('overall_status', '!=', 'Completed');
            })
            ->get();
            
        $ordersAndDisposition = OrderAndDisposition::with('case')
            ->whereHas('case', function($query) {
                $query->where('current_stage', '5: Orders & Disposition')
                    ->where('overall_status', '!=', 'Completed');
            })
            ->get();
            
        $complianceAndAwards = ComplianceAndAward::with('case')
            ->whereHas('case', function($query) {
                $query->where('current_stage', '6: Compliance & Awards')
                    ->where('overall_status', '!=', 'Completed');
            })
            ->get();
            
        $appealsAndResolutions = AppealsAndResolution::with('case')
            ->whereHas('case', function($query) {
                $query->where('current_stage', '7: Appeals & Resolution')
                    ->where('overall_status', '!=', 'Completed');
            })
            ->get();

        return view('frontend.case', compact(
            'cases',
            'inspections',
            'docketing',
            'hearingProcess',
            'reviewAndDrafting',
            'ordersAndDisposition',
            'complianceAndAwards',
            'appealsAndResolutions' 
        ));
    }

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
        // Create the case first
        $case = CaseFile::create($validated);
        
        // If the current stage is "1: Inspections", automatically create an inspection record
        if ($case->current_stage === '1: Inspections') {
            Inspection::create([
                'case_id' => $case->id,
                // All other fields will be null initially - they can be filled in later via inline editing
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
        error_log("DELETE REQUEST RECEIVED FOR ID: " . $id);
        file_put_contents(storage_path('logs/debug.log'), date('Y-m-d H:i:s') . " - Delete request for ID: " . $id . "\n", FILE_APPEND);
        
        try {
            $case = CaseFile::find($id);
            
            if (!$case) {
                error_log("CASE NOT FOUND: " . $id);
                if (request()->expectsJson()) {
                    return response()->json(['error' => 'Case not found'], 404);
                }
            }
            
            error_log("CASE FOUND: " . $case->establishment_name);
            $deleted = $case->delete();
            error_log("DELETE RESULT: " . ($deleted ? 'SUCCESS' : 'FAILED'));
            
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Case deleted successfully!'
                ]);
            }
            
            return redirect()->route('case.index')->with('success', 'Case deleted successfully!');
            
        } catch (\Exception $e) {
            error_log("DELETE ERROR: " . $e->getMessage());
            
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
                // Create docketing record
                Docketing::create([
                    'case_id' => $case->id,
                    // All other fields will be null initially
                ]);
                
                // Update case stage to Docketing
                $case->update(['current_stage' => '2: Docketing']);
                
                return redirect()->back()
                    ->with('success', 'Case successfully moved to Docketing stage')
                    ->with('active_tab', 'tab2');
                
            case '2: Docketing':
                // Create hearing process record
                HearingProcess::create([
                    'case_id' => $case->id,
                    // All other fields will be null initially - blank row
                ]);
                
                // Update case stage to Hearing
                $case->update(['current_stage' => '3: Hearing']);
                
                return redirect()->back()
                    ->with('success', 'Case successfully moved to Hearing stage')
                    ->with('active_tab', 'tab3');
                
            case '3: Hearing':
                // Create review and drafting record with required fields
                 ReviewAndDrafting::create([
                    'case_id' => $case->id,
                    // other fields will use DB defaults or null
                ]);

                
                // Update case stage
                $case->update(['current_stage' => '4: Review & Drafting']);
                
                return redirect()->back()
                    ->with('success', 'Case successfully moved to Review & Drafting stage')
                    ->with('active_tab', 'tab4');
                
            case '4: Review & Drafting':
                // Create orders and disposition record
                OrderAndDisposition::create([
                    'case_id' => $case->id,
                    // All other fields will be null initially
                ]);
                
                // Update case stage
                $case->update(['current_stage' => '5: Orders & Disposition']);
                
                return redirect()->back()
                    ->with('success', 'Case successfully moved to Orders & Disposition stage')
                    ->with('active_tab', 'tab5');
                
            case '5: Orders & Disposition':
                // Create compliance and awards record
                ComplianceAndAward::create([
                    'case_id' => $case->id,
                    // All other fields will be null initially
                ]);
                
                // Update case stage
                $case->update(['current_stage' => '6: Compliance & Awards']);
                
                return redirect()->back()
                    ->with('success', 'Case successfully moved to Compliance & Awards stage')
                    ->with('active_tab', 'tab6');
                
            case '6: Compliance & Awards':
                // Create appeals and resolution record
                AppealsAndResolution::create([
                    'case_id' => $case->id,
                    // All other fields will be null initially
                ]);
                
                // Update case stage
                $case->update(['current_stage' => '7: Appeals & Resolution']);
                
                return redirect()->back()
                    ->with('success', 'Case successfully moved to Appeals & Resolution stage')
                    ->with('active_tab', 'tab7');
                
            case '7: Appeals & Resolution':
                // Final stage - mark case as completed
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
        // Add debug logging
        Log::info('Case inline update request received', [
            'case_id' => $id,
            'request_data' => $request->all(),
            'content_type' => $request->header('Content-Type')
        ]);
        
        try {
            $case = CaseFile::findOrFail($id);
            
            // Get all input data
            $inputData = $request->all();
            
            // Remove empty strings and convert them to null
            $cleanedData = [];
            foreach ($inputData as $key => $value) {
                if ($value === '' || $value === '-') {
                    $cleanedData[$key] = null;
                } else {
                    $cleanedData[$key] = $value;
                }
            }
            
            Log::info('Cleaned data for validation', ['cleaned_data' => $cleanedData]);
            
            // Validation rules
            $validator = \Illuminate\Support\Facades\Validator::make($cleanedData, [
                'inspection_id' => 'nullable|string|max:255',
                'case_no' => 'nullable|string|max:255',
                'establishment_name' => 'nullable|string|max:500',
                'current_stage' => 'nullable|in:1: Inspections,2: Docketing,3: Hearing,4: Review & Drafting,5: Orders & Disposition,6: Compliance & Awards,7: Appeals & Resolution',
                'overall_status' => 'nullable|in:Active,Completed,Dismissed',
            ]);

            if ($validator->fails()) {
                Log::warning('Case validation failed', [
                    'errors' => $validator->errors(),
                    'data' => $cleanedData
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed: ' . $validator->errors()->first(),
                    'errors' => $validator->errors()
                ], 422);
            }

            $validatedData = $validator->validated();
            
            Log::info('Case update data validated', [
                'validated_data' => $validatedData
            ]);
            
            // Update the case record
            $case->update($validatedData);
            Log::info('Case updated successfully');

            // Reload the case
            $case->refresh();

            // Prepare response data with proper formatting
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
            Log::error('Case inline update failed: ' . $e->getMessage(), [
                'case_id' => $id,
                'request_data' => $request->all(),
                'error' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update case: ' . $e->getMessage()
            ], 500);
        }
    }
}