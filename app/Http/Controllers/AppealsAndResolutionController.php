<?php

namespace App\Http\Controllers;

use App\Models\AppealsAndResolution;
use App\Models\CaseFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class AppealsAndResolutionController extends Controller
{
    /**
     * Display a listing of appeals and resolution in the combined case view.
     */
    public function index()
    {
        $cases = CaseFile::all();
        $appealsAndResolutions = AppealsAndResolution::with('case')->get();
        
        return view('frontend.case', compact('cases', 'appealsAndResolutions'));
    }

    /**
     * Show the form for creating a new appeals and resolution record (combined view).
     */
    public function create()
    {
        $cases = CaseFile::all();
        $appealsAndResolutions = AppealsAndResolution::with('case')->get();
        return view('frontend.case', compact('cases', 'appealsAndResolutions'));
    }

    /**
     * Store a newly created appeals and resolution record in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'case_id' => 'required|exists:cases,id',
            'date_returned_case_mgmt' => 'nullable|date',
            'review_ct_cnpc' => 'nullable|string|max:255',
            'date_received_drafter_finalization_2nd' => 'nullable|date',
            'date_returned_case_mgmt_signature_2nd' => 'nullable|date',
            'date_order_2nd_cnpc' => 'nullable|date',
            'released_date_2nd_cnpc' => 'nullable|date',
            'date_forwarded_malsu' => 'nullable|date',
            'motion_reconsideration_date' => 'nullable|date',
            'date_received_malsu' => 'nullable|date',
            'date_resolution_mr' => 'nullable|date',
            'released_date_resolution_mr' => 'nullable|date',
            'date_appeal_received_records' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        AppealsAndResolution::create($request->all());

        return redirect()->route('appeals-and-resolution.index')
            ->with('success', 'Appeals and Resolution record created successfully.')
            ->with('active_tab', 'appeals-and-resolution');
    }

    /**
     * Display the specified appeals and resolution record in the combined view.
     */
    public function show($id)
    {
        $appealsAndResolution = AppealsAndResolution::with('case')->findOrFail($id);
        
        // Access case data through the relationship
        $inspectionId = $appealsAndResolution->case->inspection_id;
        $establishmentName = $appealsAndResolution->case->establishment_name;
        
        $cases = CaseFile::all();
        $appealsAndResolutions = AppealsAndResolution::with('case')->get();
        return view('frontend.case', compact('cases', 'appealsAndResolutions', 'appealsAndResolution'));
    }

    /**
     * Show the form for editing the specified appeals and resolution record in combined view.
     */
    public function edit($id)
    {
        $appealsAndResolution = AppealsAndResolution::with('case')->findOrFail($id);
        
        // Access case data through the relationship
        $inspectionId = $appealsAndResolution->case->inspection_id;
        $establishmentName = $appealsAndResolution->case->establishment_name;
        
        $cases = CaseFile::all();
        $appealsAndResolutions = AppealsAndResolution::with('case')->get();
        return view('frontend.case', compact('cases', 'appealsAndResolutions', 'appealsAndResolution'));
    }

    /**
     * Update the specified appeals and resolution record in storage.
     */
    public function update(Request $request, $id)
    {
        $appealsAndResolution = AppealsAndResolution::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'case_id' => 'required|exists:cases,id',
            'date_returned_case_mgmt' => 'nullable|date',
            'review_ct_cnpc' => 'nullable|string|max:255',
            'date_received_drafter_finalization_2nd' => 'nullable|date',
            'date_returned_case_mgmt_signature_2nd' => 'nullable|date',
            'date_order_2nd_cnpc' => 'nullable|date',
            'released_date_2nd_cnpc' => 'nullable|date',
            'date_forwarded_malsu' => 'nullable|date',
            'motion_reconsideration_date' => 'nullable|date',
            'date_received_malsu' => 'nullable|date',
            'date_resolution_mr' => 'nullable|date',
            'released_date_resolution_mr' => 'nullable|date',
            'date_appeal_received_records' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $appealsAndResolution->update($request->all());

        return redirect()->route('appeals-and-resolution.index')
            ->with('success', 'Appeals and Resolution record updated successfully.')
            ->with('active_tab', 'appeals-and-resolution');
    }

    /**
     * Remove the specified appeals and resolution record from storage.
     */
    public function destroy($id)
    {
        try {
            $appealsAndResolution = AppealsAndResolution::findOrFail($id);
            $appealsAndResolution->delete();
            Log::info('Appeals and Resolution ID: ' . $id . ' deleted successfully.');
            
            return redirect()->route('case.index')
                ->with('success', 'Appeals and Resolution record deleted successfully.')
                ->with('active_tab', 'appeals-and-resolution');
        } catch (\Exception $e) {
            Log::error('Error deleting Appeals and Resolution ID: ' . $id . ' - ' . $e->getMessage());
            return redirect()->route('case.index')
                ->with('error', 'Failed to delete Appeals and Resolution record: ' . $e->getMessage())
                ->with('active_tab', 'appeals-and-resolution');
        }
    }
}