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
     * Only show inspections where the related case's current_stage is "1: Inspections"
     */
    public function index()
    {
        $cases = CaseFile::all();
        
        // UPDATED: Filter inspections to only show those in "1: Inspections" stage
        $inspections = Inspection::with('case')
            ->whereHas('case', function($query) {
                $query->where('current_stage', '1: Inspections');
            })
            ->get();
        
        return view('frontend.case', compact('cases', 'inspections'));
    }

    /**
     * Show the form for creating a new inspection (combined view).
     */
    public function create()
    {
        $cases = CaseFile::all();
        
        // UPDATED: Filter inspections for create view as well
        $inspections = Inspection::with('case')
            ->whereHas('case', function($query) {
                $query->where('current_stage', '1: Inspections');
            })
            ->get();
            
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
            ->with('active_tab', 'inspections');
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
        
        // UPDATED: Filter inspections for show view
        $inspections = Inspection::with('case')
            ->whereHas('case', function($query) {
                $query->where('current_stage', '1: Inspections');
            })
            ->get();
            
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
        
        // UPDATED: Filter inspections for edit view
        $inspections = Inspection::with('case')
            ->whereHas('case', function($query) {
                $query->where('current_stage', '1: Inspections');
            })
            ->get();
            
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

    /**
     * Update inspection data inline via AJAX
     */
    public function inlineUpdate(Request $request, Inspection $inspection)
    {
        try {
            $validatedData = $request->validate([
                'inspection_id' => 'nullable|string|max:255',
                'establishment_name' => 'nullable|string|max:255',
                'po_office' => 'nullable|string|max:255',
                'inspector_name' => 'nullable|string|max:255',
                'inspector_authority_no' => 'nullable|string|max:255',
                'date_of_inspection' => 'nullable|date',
                'date_of_nr' => 'nullable|date',
                'lapse_20_day_period' => 'nullable|date',
                'twg_ali' => 'nullable|string|max:255',
            ]);

            // Handle case-related fields (inspection_id and establishment_name)
            if (isset($validatedData['inspection_id']) || isset($validatedData['establishment_name'])) {
                if ($inspection->case) {
                    $caseData = [];
                    if (isset($validatedData['inspection_id'])) {
                        $caseData['inspection_id'] = $validatedData['inspection_id'];
                    }
                    if (isset($validatedData['establishment_name'])) {
                        $caseData['establishment_name'] = $validatedData['establishment_name'];
                    }
                    
                    $inspection->case->update($caseData);
                    
                    // Remove case fields from inspection data
                    unset($validatedData['inspection_id']);
                    unset($validatedData['establishment_name']);
                }
            }

            // Update inspection fields
            if (!empty($validatedData)) {
                $inspection->update($validatedData);
            }

            // Reload the inspection with case data
            $inspection->load('case');

            // Prepare response data with both inspection and case fields
            $responseData = $inspection->toArray();
            if ($inspection->case) {
                $responseData['inspection_id'] = $inspection->case->inspection_id;
                $responseData['establishment_name'] = $inspection->case->establishment_name;
            }

            Log::info('Inspection inline update successful', [
                'inspection_id' => $inspection->id,
                'updated_data' => $validatedData
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Inspection updated successfully',
                'data' => $responseData
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation error in inspection inline update', [
                'inspection_id' => $inspection->id,
                'errors' => $e->errors()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Error in inspection inline update', [
                'inspection_id' => $inspection->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating the inspection'
            ], 500);
        }
    }
}