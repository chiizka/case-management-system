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
     * Display a listing of inspections.
     * Since we use CasesController for the combined view, this just redirects there.
     */
    public function index()
    {
        return redirect()->route('case.index')
            ->with('active_tab', 'inspections');
    }

    public function create()
    {
        return redirect()->route('case.index')
            ->with('active_tab', 'inspections');
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
            return redirect()->route('case.index')
                ->withErrors($validator)
                ->withInput()
                ->with('active_tab', 'inspections');
        }

        try {
            Inspection::create($request->all());
            
            return redirect()->route('case.index')
                ->with('success', 'Inspection created successfully.')
                ->with('active_tab', 'inspections');
        } catch (\Exception $e) {
            Log::error('Error creating inspection: ' . $e->getMessage());
            
            return redirect()->route('case.index')
                ->with('error', 'Failed to create inspection: ' . $e->getMessage())
                ->with('active_tab', 'inspections');
        }
    }

    /**
     * Display the specified inspection.
     * Since we use the combined view, redirect there with the inspections tab active.
     */
    public function show($id)
    {
        try {
            $inspection = Inspection::with('case')->findOrFail($id);
            
            return redirect()->route('case.index')
                ->with('active_tab', 'inspections')
                ->with('highlighted_id', $id);
        } catch (\Exception $e) {
            return redirect()->route('case.index')
                ->with('error', 'Inspection not found.')
                ->with('active_tab', 'inspections');
        }
    }

    /**
     * Show the form for editing the specified inspection.
     * Since we use the combined view, redirect there with the inspections tab active.
     */
    public function edit($id)
    {
        try {
            $inspection = Inspection::with('case')->findOrFail($id);
            
            // Return JSON for AJAX requests (used by your inline editing)
            if (request()->expectsJson()) {
                return response()->json([
                    'id' => $inspection->id,
                    'case_id' => $inspection->case_id,
                    'inspection_id' => $inspection->case->inspection_id ?? '',
                    'establishment_name' => $inspection->case->establishment_name ?? '',
                    'po_office' => $inspection->po_office,
                    'inspector_name' => $inspection->inspector_name,
                    'inspector_authority_no' => $inspection->inspector_authority_no,
                    'date_of_inspection' => $inspection->date_of_inspection,
                    'date_of_nr' => $inspection->date_of_nr,
                    'lapse_20_day_period' => $inspection->lapse_20_day_period,
                    'twg_ali' => $inspection->twg_ali,
                ]);
            }
            
            return redirect()->route('case.index')
                ->with('active_tab', 'inspections')
                ->with('edit_id', $id);
        } catch (\Exception $e) {
            if (request()->expectsJson()) {
                return response()->json(['error' => 'Inspection not found'], 404);
            }
            
            return redirect()->route('case.index')
                ->with('error', 'Inspection not found.')
                ->with('active_tab', 'inspections');
        }
    }

    /**
     * Update the specified inspection in storage.
     */
    public function update(Request $request, $id)
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
            return redirect()->route('case.index')
                ->withErrors($validator)
                ->withInput()
                ->with('active_tab', 'inspections');
        }

        try {
            $inspection = Inspection::findOrFail($id);
            $inspection->update($request->all());

            return redirect()->route('case.index')
                ->with('success', 'Inspection updated successfully.')
                ->with('active_tab', 'inspections');
        } catch (\Exception $e) {
            Log::error('Error updating inspection ID: ' . $id . ' - ' . $e->getMessage());
            
            return redirect()->route('case.index')
                ->with('error', 'Failed to update inspection: ' . $e->getMessage())
                ->with('active_tab', 'inspections');
        }
    }

    /**
     * Remove the specified inspection from storage.
     * FIXED: Now redirects to CasesController which has all the data needed.
     */
    public function destroy($id)
    {
        try {
            $inspection = Inspection::findOrFail($id);
            $inspection->delete();
            Log::info('Inspection ID: ' . $id . ' deleted successfully.');
            
            // FIXED: Redirect to CasesController which has all tab data
            return redirect()->route('case.index')
                ->with('success', 'Inspection deleted successfully.')
                ->with('active_tab', 'inspections');
        } catch (\Exception $e) {
            Log::error('Error deleting inspection ID: ' . $id . ' - ' . $e->getMessage());
            
            // FIXED: Redirect to CasesController which has all tab data
            return redirect()->route('case.index')
                ->with('error', 'Failed to delete inspection: ' . $e->getMessage())
                ->with('active_tab', 'inspections');
        }
    }

    /**
     * Handle inline updates via AJAX.
     * This method handles the inline editing functionality from your JavaScript.
     */
    public function inlineUpdate(Request $request, $id)
    {
        // Add debug logging
        Log::info('Inspection inline update request received', [
            'inspection_id' => $id,
            'request_data' => $request->all(),
            'content_type' => $request->header('Content-Type')
        ]);
        
        try {
            $inspection = Inspection::findOrFail($id);
            
            // Get all input data
            $inputData = $request->all();
            
            // Remove lapse_20_day_period from input data since it's computed
            unset($inputData['lapse_20_day_period']);
            
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
                'inspection_id' => 'nullable|string|max:255',
                'establishment_name' => 'nullable|string|max:500',
                'po_office' => 'nullable|string|max:255',
                'inspector_name' => 'nullable|string|max:255',
                'inspector_authority_no' => 'nullable|string|max:255',
                'date_of_inspection' => 'nullable|date',
                'date_of_nr' => 'nullable|date',
                'twg_ali' => 'nullable|string|max:255',
            ]);

            if ($validator->fails()) {
                Log::warning('Validation failed', [
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
            
            // Separate data for case and inspection updates
            $caseData = [];
            $inspectionData = [];
            
            foreach ($validatedData as $field => $value) {
                if (in_array($field, ['inspection_id', 'establishment_name'])) {
                    $caseData[$field] = $value;
                } else {
                    $inspectionData[$field] = $value;
                }
            }
            
            Log::info('Update data separated', [
                'case_data' => $caseData,
                'inspection_data' => $inspectionData
            ]);
            
            // Update the related case if there's case data to update
            if (!empty($caseData) && $inspection->case) {
                $inspection->case->update($caseData);
                Log::info('Case updated successfully');
            }
            
            // Update the inspection record with remaining fields
            if (!empty($inspectionData)) {
                $inspection->update($inspectionData);
                Log::info('Inspection updated successfully');
            }

            // CRITICAL: Refresh the inspection from database to get computed field
            $inspection->refresh();
            
            // Also reload the case relationship
            $inspection->load('case');

            Log::info('Inspection refreshed, lapse_20_day_period value: ' . $inspection->lapse_20_day_period);

            // Prepare response data with proper formatting
            $responseData = [
                'inspection_id' => $inspection->case->inspection_id ?? '-',
                'establishment_name' => $inspection->case->establishment_name ?? '-',
                'po_office' => $inspection->po_office ?? '-',
                'inspector_name' => $inspection->inspector_name ?? '-',
                'inspector_authority_no' => $inspection->inspector_authority_no ?? '-',
                'date_of_inspection' => $inspection->date_of_inspection ? \Carbon\Carbon::parse($inspection->date_of_inspection)->format('Y-m-d') : '-',
                'date_of_nr' => $inspection->date_of_nr ? \Carbon\Carbon::parse($inspection->date_of_nr)->format('Y-m-d') : '-',
                // This will now have the correct computed value
                'lapse_20_day_period' => $inspection->lapse_20_day_period ? \Carbon\Carbon::parse($inspection->lapse_20_day_period)->format('Y-m-d') : '-',
                'twg_ali' => $inspection->twg_ali ?? '-',
            ];

            return response()->json([
                'success' => true,
                'message' => 'Inspection updated successfully!',
                'data' => $responseData
            ]);

        } catch (\Exception $e) {
            Log::error('Inline update failed: ' . $e->getMessage(), [
                'inspection_id' => $id,
                'request_data' => $request->all(),
                'error' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update inspection: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get inspection data for AJAX requests.
     * Used by your frontend for populating edit forms.
     */
    public function getInspection($id)
    {
        try {
            $inspection = Inspection::with('case')->findOrFail($id);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $inspection->id,
                    'case_id' => $inspection->case_id,
                    'inspection_id' => $inspection->case->inspection_id ?? '',
                    'establishment_name' => $inspection->case->establishment_name ?? '',
                    'po_office' => $inspection->po_office,
                    'inspector_name' => $inspection->inspector_name,
                    'inspector_authority_no' => $inspection->inspector_authority_no,
                    'date_of_inspection' => $inspection->date_of_inspection,
                    'date_of_nr' => $inspection->date_of_nr,
                    'lapse_20_day_period' => $inspection->lapse_20_day_period,
                    'twg_ali' => $inspection->twg_ali,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Inspection not found'
            ], 404);
        }
    }
}