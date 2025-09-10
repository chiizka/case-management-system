<?php

namespace App\Http\Controllers;

use App\Models\CaseFile;
use App\Models\Inspection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class InspectionsController extends Controller
{
    /**
     * Display a listing of inspections in the combined case view.
     */
public function index()
{
    $cases = CaseFile::all();
    $inspections = Inspection::with('case')->get();
    
    // This will dump the data and stop execution
    dd([
        'cases_count' => $cases->count(),
        'inspections_count' => $inspections->count(),
        'inspections_data' => $inspections->toArray()
    ]);
    
    return view('frontend.case', compact('cases', 'inspections'));
}


    /**
     * Show the form for creating a new inspection (combined view).
     */
    public function create()
    {
        $cases = CaseFile::all();
        $inspections = Inspection::with('case')->get();
        return view('frontend.case', compact('cases', 'inspections'));
    }

    /**
     * Store a newly created inspection in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'case_id' => 'required|exists:cases,id',
            'po_office' => 'nullable|string|max:255',
            'inspector_name' => 'nullable|string|max:255',
            'inspector_authority_no' => 'nullable|string|max:255',
            'date_of_inspection' => 'nullable|date',
            'date_of_nr' => 'nullable|date',
            'lapse_20_day_period' => 'nullable|date',
            'twg_ali' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        Inspection::create($request->all());

        return redirect()->route('inspections.index')
            ->with('success', 'Inspection created successfully.')
            ->with('active_tab', 'inspections'); // Add this to activate the inspections tab
    }

    /**
     * Display the specified inspection in the combined view.
     */
    public function show($id)
    {
        $inspection = Inspection::with('case')->findOrFail($id);
        
        // Access case data through the relationship
        $inspectionId = $inspection->case->inspection_id;
        $establishmentName = $inspection->case->establishment_name;
        
        $cases = CaseFile::all();
        $inspections = Inspection::with('case')->get();
        return view('frontend.case', compact('cases', 'inspections', 'inspection'));
    }

    /**
     * Show the form for editing the specified inspection in combined view.
     */
    public function edit($id)
    {
        $inspection = Inspection::with('case')->findOrFail($id);
        
        // Access case data through the relationship
        $inspectionId = $inspection->case->inspection_id;
        $establishmentName = $inspection->case->establishment_name;
        
        $cases = CaseFile::all();
        $inspections = Inspection::with('case')->get();
        return view('frontend.case', compact('cases', 'inspections', 'inspection'));
    }

    /**
     * Update the specified inspection in storage.
     */
    public function update(Request $request, $id)
    {
        $inspection = Inspection::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'case_id' => 'required|exists:cases,id',
            'po_office' => 'nullable|string|max:255',
            'inspector_name' => 'nullable|string|max:255',
            'inspector_authority_no' => 'nullable|string|max:255',
            'date_of_inspection' => 'nullable|date',
            'date_of_nr' => 'nullable|date',
            'lapse_20_day_period' => 'nullable|date',
            'twg_ali' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $inspection->update($request->all());

        return redirect()->route('inspections.index')
            ->with('success', 'Inspection updated successfully.')
            ->with('active_tab', 'inspections');
    }

    /**
     * Remove the specified inspection from storage.
     */
public function destroy($id)
{
    try {
        $inspection = Inspection::findOrFail($id);
        $inspection->delete();
        Log::info('Inspection ID: ' . $id . ' deleted successfully.');
        
        return redirect()->route('case.index')
            ->with('success', 'Inspection deleted successfully.')
            ->with('active_tab', 'inspections');
    } catch (\Exception $e) {
        Log::error('Error deleting inspection ID: ' . $id . ' - ' . $e->getMessage());
        return redirect()->route('case.index')
            ->with('error', 'Failed to delete inspection: ' . $e->getMessage())
            ->with('active_tab', 'inspections');
    }
}
}