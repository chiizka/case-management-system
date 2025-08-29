<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CaseFile;
class CasesController extends Controller
{
    public function case(){
        $cases = CaseFile::all(); // Get all cases from database
        return view('frontend.case', compact('cases'));

    }

    public function store(Request $request)
    {
        // Validate the request
        $validated = $request->validate([
            'case_number' => 'required|string|max:255',
            'case_status' => 'required|string',
            'case_type' => 'required|string|max:255',
            'complainant' => 'required|string',
            'respondent' => 'required|string', 
            'case_details' => 'required|string',
            'date_filed' => 'required|date',
        ]);

        // Create the case
        CaseFile::create($validated);
        
        // Redirect back with success message
        return redirect()->route('case.index')->with('success', 'Case created successfully!');
    }
    
}
