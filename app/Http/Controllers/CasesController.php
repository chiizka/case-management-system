<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CaseFile;
use App\Models\Inspection;
use App\Models\Docketing;
use App\Models\HearingProcess;
use App\Models\ReviewAndDrafting; 
use App\Models\OrderAndDisposition; 
use Illuminate\Support\Facades\Log;

class CasesController extends Controller
{
    public function case()
    {
        $cases = CaseFile::all();
        $inspections = Inspection::with('case')->get();
        $docketing = Docketing::with('case')->get();
        $hearingProcess = HearingProcess::with('case')->get();
        $reviewAndDrafting = ReviewAndDrafting::with('case')->get();
        $ordersAndDisposition = OrderAndDisposition::with('case')->get();

        return view('frontend.case', compact(
            'cases',
            'inspections',
            'docketing',
            'hearingProcess',
            'reviewAndDrafting',
            'ordersAndDisposition' 
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

        CaseFile::create($validated);
        
        return redirect()->route('case.index')->with('success', 'Case created successfully!');
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
        // Force logging to work
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
}