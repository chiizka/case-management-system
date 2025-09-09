<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CaseFile;
use App\Models\Inspection;

class CasesController extends Controller
{
public function case()
{
    $cases = CaseFile::all();
    $inspections = Inspection::with('case')->get(); // Add this line
    
    return view('frontend.case', compact('cases', 'inspections')); // Add 'inspections' here
}

    public function store(Request $request)
    {
        $validated = $request->validate([
            'inspection_id' => 'required|string|max:255',
            'case_no' => 'nullable|string|max:255',
            'establishment_name' => 'required|string|max:255',
            'current_stage' => 'required|integer|between:1,7',
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
            'current_stage' => 'required|integer|between:1,7',
            'overall_status' => 'required|in:Active,Completed,Dismissed',
        ]);

        $case = CaseFile::findOrFail($id);
        $case->update($validated);
        
        return redirect()->route('case.index')->with('success', 'Case updated successfully!');
    }

    public function destroy($id)
{
    try {
        $case = CaseFile::findOrFail($id);
        $case->delete();
        
        // Check if request expects JSON (AJAX)
        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Case deleted successfully!'
            ]);
        }
        
        return redirect()->route('case.index')->with('success', 'Case deleted successfully!');
        
    } catch (\Exception $e) {
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