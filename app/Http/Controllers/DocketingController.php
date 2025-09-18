<?php

namespace App\Http\Controllers;

use App\Models\docketing; // Consider renaming this to 'Docketing' for consistency
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
        // Redirect to unified case view instead of loading data here
        return redirect()->route('case.index')
            ->with('active_tab', 'docketing');
    }

    /**
     * Show the form for creating a new docketing (combined view).
     */
    public function create()
    {
        return redirect()->route('case.index')
            ->with('active_tab', 'docketing');
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
            return redirect()->route('case.index')
                ->withErrors($validator)
                ->withInput()
                ->with('active_tab', 'docketing');
        }

        try {
            docketing::create($request->all());
            
            return redirect()->route('case.index')
                ->with('success', 'Docketing created successfully.')
                ->with('active_tab', 'docketing');
        } catch (\Exception $e) {
            Log::error('Error creating docketing: ' . $e->getMessage());
            
            return redirect()->route('case.index')
                ->with('error', 'Failed to create docketing: ' . $e->getMessage())
                ->with('active_tab', 'docketing');
        }
    }

    /**
     * Display the specified docketing in the combined view.
     */
    public function show($id)
    {
        try {
            $docketingRecord = docketing::with('case')->findOrFail($id);
            
            return redirect()->route('case.index')
                ->with('active_tab', 'docketing')
                ->with('highlighted_id', $id);
        } catch (\Exception $e) {
            return redirect()->route('case.index')
                ->with('error', 'Docketing not found.')
                ->with('active_tab', 'docketing');
        }
    }

    /**
     * Show the form for editing the specified docketing.
     */
    public function edit($id)
    {
        try {
            $docketingRecord = docketing::with('case')->findOrFail($id);
            
            // Return JSON for AJAX requests
            if (request()->expectsJson()) {
                return response()->json([
                    'id' => $docketingRecord->id,
                    'case_id' => $docketingRecord->case_id,
                    'inspection_id' => $docketingRecord->case->inspection_id ?? '',
                    'establishment_name' => $docketingRecord->case->establishment_name ?? '',
                    'pct_for_docketing' => $docketingRecord->pct_for_docketing,
                    'date_scheduled_docketed' => $docketingRecord->date_scheduled_docketed,
                    'aging_docket' => $docketingRecord->aging_docket,
                    'status_docket' => $docketingRecord->status_docket,
                    'hearing_officer_mis' => $docketingRecord->hearing_officer_mis,
                ]);
            }
            
            return redirect()->route('case.index')
                ->with('active_tab', 'docketing')
                ->with('edit_id', $id);
        } catch (\Exception $e) {
            if (request()->expectsJson()) {
                return response()->json(['error' => 'Docketing not found'], 404);
            }
            
            return redirect()->route('case.index')
                ->with('error', 'Docketing not found.')
                ->with('active_tab', 'docketing');
        }
    }

    /**
     * Update the specified docketing in storage.
     */
    public function update(Request $request, $id)
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
            return redirect()->route('case.index')
                ->withErrors($validator)
                ->withInput()
                ->with('active_tab', 'docketing');
        }

        try {
            $docketingRecord = docketing::findOrFail($id);
            $docketingRecord->update($request->all());

            return redirect()->route('case.index')
                ->with('success', 'Docketing updated successfully.')
                ->with('active_tab', 'docketing');
        } catch (\Exception $e) {
            Log::error('Error updating docketing ID: ' . $id . ' - ' . $e->getMessage());
            
            return redirect()->route('case.index')
                ->with('error', 'Failed to update docketing: ' . $e->getMessage())
                ->with('active_tab', 'docketing');
        }
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

            return redirect()->route('case.index')
                ->with('success', 'Docketing deleted successfully.')
                ->with('active_tab', 'docketing');
        } catch (\Exception $e) {
            Log::error('Error deleting docketing ID: ' . $id . ' - ' . $e->getMessage());
            
            return redirect()->route('case.index')
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
            
            // Remove readonly fields (these come from the case relationship)
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
                'pct_for_docketing' => 'nullable|numeric|min:0',
                'date_scheduled_docketed' => 'nullable|date',
                'aging_docket' => 'nullable|numeric|min:0',
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

    /**
     * Get docketing data for AJAX requests.
     */
    public function getDocketing($id)
    {
        try {
            $docketing = docketing::with('case')->findOrFail($id);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $docketing->id,
                    'case_id' => $docketing->case_id,
                    'inspection_id' => $docketing->case->inspection_id ?? '',
                    'establishment_name' => $docketing->case->establishment_name ?? '',
                    'pct_for_docketing' => $docketing->pct_for_docketing,
                    'date_scheduled_docketed' => $docketing->date_scheduled_docketed,
                    'aging_docket' => $docketing->aging_docket,
                    'status_docket' => $docketing->status_docket,
                    'hearing_officer_mis' => $docketing->hearing_officer_mis,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Docketing not found'
            ], 404);
        }
    }
}