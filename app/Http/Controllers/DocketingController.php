<?php

namespace App\Http\Controllers;

use App\Models\docketing;
use App\Models\CaseFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class DocketingController extends Controller
{
    /**
     * Display a listing of docketings in the combined case view.
     */
    public function index()
    {
        $cases = CaseFile::all();
        $docketing = docketing::with('case')->get(); // Changed to singular to match view

        return view('frontend.case', compact('cases', 'docketing'));
    }

    /**
     * Show the form for creating a new docketing (combined view).
     */
    public function create()
    {
        $cases = CaseFile::all();
        $docketing = docketing::with('case')->get(); // Changed to singular
        return view('frontend.case', compact('cases', 'docketing'));
    }

    /**
     * Store a newly created docketing in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'case_id' => 'required|exists:cases,id',
            'pct_for_docketing' => 'nullable|numeric',
            'date_scheduled_docketed' => 'nullable|date',
            'aging_docket' => 'nullable|numeric',
            'status_docket' => 'nullable|string|max:255',
            'hearing_officer_mis' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        docketing::create($request->all());

        return redirect()->route('docketing.index')
            ->with('success', 'Docketing created successfully.')
            ->with('active_tab', 'docketing');
    }

    /**
     * Display the specified docketing in the combined view.
     */
    public function show($id)
    {
        $docketingRecord = docketing::with('case')->findOrFail($id);

        // Access case data through the relationship - FIXED
        $inspectionId = $docketingRecord->case->inspection_id ?? null;
        $establishment = $docketingRecord->case->establishment_name ?? null;

        $cases = CaseFile::all();
        $docketing = docketing::with('case')->get(); // Changed to singular
        return view('frontend.case', compact('cases', 'docketing', 'docketingRecord'));
    }

    /**
     * Show the form for editing the specified docketing in combined view.
     */
    public function edit($id)
    {
        $docketingRecord = docketing::with('case')->findOrFail($id);

        // Access case data through the relationship - FIXED
        $inspectionId = $docketingRecord->case->inspection_id ?? null;
        $establishment = $docketingRecord->case->establishment_name ?? null;

        $cases = CaseFile::all();
        $docketing = docketing::with('case')->get(); // Changed to singular
        return view('frontend.case', compact('cases', 'docketing', 'docketingRecord'));
    }

    /**
     * Update the specified docketing in storage.
     */
    public function update(Request $request, $id)
    {
        $docketingRecord = docketing::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'case_id' => 'required|exists:cases,id',
            'pct_for_docketing' => 'nullable|numeric',
            'date_scheduled_docketed' => 'nullable|date',
            'aging_docket' => 'nullable|numeric',
            'status_docket' => 'nullable|string|max:255',
            'hearing_officer_mis' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $docketingRecord->update($request->all());

        return redirect()->route('docketing.index')
            ->with('success', 'Docketing updated successfully.')
            ->with('active_tab', 'docketing');
    }

    /**
     * Remove the specified docketing from storage.
     */
    public function destroy($id)
    {
        try {
            $docketingRecord = docketing::findOrFail($id);
            $docketingRecord->delete();
            Log::info('Docketing ID: ' . $id . ' deleted successfully.');

            return redirect()->route('docketing.index')
                ->with('success', 'Docketing deleted successfully.')
                ->with('active_tab', 'docketing');
        } catch (\Exception $e) {
            Log::error('Error deleting docketing ID: ' . $id . ' - ' . $e->getMessage());
            return redirect()->route('docketing.index')
                ->with('error', 'Failed to delete docketing: ' . $e->getMessage())
                ->with('active_tab', 'docketing');
        }
    }

    /**
 * Update docketing record inline via AJAX
 */
public function inlineUpdate(Request $request, $id)
{
    Log::info('Docketing inline update request received', [
        'docketing_id' => $id,
        'request_data' => $request->all(),
        'content_type' => $request->header('Content-Type')
    ]);
    
    try {
        $docketing = docketing::findOrFail($id);
        
        // Get all input data
        $inputData = $request->all();
        
        // Remove readonly fields (inspection_id and establishment_name come from case)
        unset($inputData['inspection_id']);
        unset($inputData['establishment_name']);
        
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
        $validator = Validator::make($cleanedData, [
            'pct_for_docketing' => 'nullable|numeric',
            'date_scheduled_docketed' => 'nullable|date',
            'aging_docket' => 'nullable|numeric',
            'status_docket' => 'nullable|string|max:255',
            'hearing_officer_mis' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            Log::warning('Docketing validation failed', [
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
        
        Log::info('Docketing update data validated', [
            'validated_data' => $validatedData
        ]);
        
        // Update the docketing record
        $docketing->update($validatedData);
        Log::info('Docketing updated successfully');

        // Refresh to get any computed fields
        $docketing->refresh();
        
        // Load the case relationship
        $docketing->load('case');

        // Prepare response data with proper formatting
        $responseData = [
            'inspection_id' => $docketing->case->inspection_id ?? '-',
            'establishment_name' => $docketing->case->establishment_name ?? '-',
            'pct_for_docketing' => $docketing->pct_for_docketing ?? '-',
            'date_scheduled_docketed' => $docketing->date_scheduled_docketed ? \Carbon\Carbon::parse($docketing->date_scheduled_docketed)->format('Y-m-d') : '-',
            'aging_docket' => $docketing->aging_docket ?? '-',
            'status_docket' => $docketing->status_docket ?? 'Pending',
            'hearing_officer_mis' => $docketing->hearing_officer_mis ?? '-',
        ];

        return response()->json([
            'success' => true,
            'message' => 'Docketing updated successfully!',
            'data' => $responseData
        ]);

    } catch (\Exception $e) {
        Log::error('Docketing inline update failed: ' . $e->getMessage(), [
            'docketing_id' => $id,
            'request_data' => $request->all(),
            'error' => $e->getTraceAsString()
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Failed to update docketing: ' . $e->getMessage()
        ], 500);
    }
}
}