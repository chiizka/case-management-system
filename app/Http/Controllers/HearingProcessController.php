<?php

namespace App\Http\Controllers;

use App\Models\HearingProcess;
use App\Models\CaseFile;
use App\Helpers\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class HearingProcessController extends Controller
{
    /**
     * Display a listing of hearing processes in the combined case view.
     */
    public function index()
    {
        ActivityLogger::logAction(
            'VIEW',
            'HearingProcess',
            null,
            'Viewed hearing process list page'
        );

        // Redirect to unified case view instead of loading data here
        return redirect()->route('case.index')
            ->with('active_tab', 'hearing');
    }

    /**
     * Show the form for creating a new hearing process (combined view).
     */
    public function create()
    {
        return redirect()->route('case.index')
            ->with('active_tab', 'hearing');
    }

    /**
     * Store a newly created hearing process in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'case_id' => 'required|exists:cases,id',
            'hearing_date' => 'nullable|date',
            'hearing_time' => 'nullable|string',
            'hearing_officer' => 'nullable|string|max:255',
            'hearing_venue' => 'nullable|string|max:255',
            'status' => 'nullable|string|max:255',
            'respondent_present' => 'nullable|boolean',
            'complainant_present' => 'nullable|boolean',
            'hearing_notes' => 'nullable|string',
            'next_hearing_date' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return redirect()->route('case.index')
                ->withErrors($validator)
                ->withInput()
                ->with('active_tab', 'hearing');
        }

        try {
            $hearingProcess = HearingProcess::create($request->all());
            $case = $hearingProcess->case;

            ActivityLogger::logAction(
                'CREATE',
                'HearingProcess',
                $case->inspection_id ?? $hearingProcess->id,
                'Created new hearing process record',
                [
                    'establishment' => $case->establishment_name ?? 'Unknown',
                    'hearing_date' => $request->hearing_date ?? 'Not set',
                    'hearing_officer' => $request->hearing_officer ?? 'Not assigned',
                    'status' => $request->status ?? 'Not set'
                ]
            );
            
            return redirect()->route('case.index')
                ->with('success', 'Hearing process created successfully.')
                ->with('active_tab', 'hearing');
        } catch (\Exception $e) {
            Log::error('Error creating hearing process: ' . $e->getMessage());
            
            return redirect()->route('case.index')
                ->with('error', 'Failed to create hearing process: ' . $e->getMessage())
                ->with('active_tab', 'hearing');
        }
    }

    /**
     * Display the specified hearing process in the combined view.
     */
    public function show($id)
    {
        try {
            $hearingProcess = HearingProcess::with('case')->findOrFail($id);
            
            ActivityLogger::logAction(
                'VIEW',
                'HearingProcess',
                $hearingProcess->case->inspection_id ?? $id,
                'Viewed hearing process details',
                [
                    'establishment' => $hearingProcess->case->establishment_name ?? 'Unknown'
                ]
            );
            
            return redirect()->route('case.index')
                ->with('active_tab', 'hearing')
                ->with('highlighted_id', $id);
        } catch (\Exception $e) {
            return redirect()->route('case.index')
                ->with('error', 'Hearing process not found.')
                ->with('active_tab', 'hearing');
        }
    }

    /**
     * Show the form for editing the specified hearing process.
     */
    public function edit($id)
    {
        try {
            $hearingProcess = HearingProcess::with('case')->findOrFail($id);
            
            ActivityLogger::logAction(
                'VIEW',
                'HearingProcess',
                $hearingProcess->case->inspection_id ?? $id,
                'Opened hearing process record for editing',
                [
                    'establishment' => $hearingProcess->case->establishment_name ?? 'Unknown'
                ]
            );
            
            // Return JSON for AJAX requests
            if (request()->expectsJson()) {
                return response()->json([
                    'id' => $hearingProcess->id,
                    'case_id' => $hearingProcess->case_id,
                    'inspection_id' => $hearingProcess->case->inspection_id ?? '',
                    'establishment_name' => $hearingProcess->case->establishment_name ?? '',
                    'hearing_date' => $hearingProcess->hearing_date,
                    'hearing_time' => $hearingProcess->hearing_time,
                    'hearing_officer' => $hearingProcess->hearing_officer,
                    'hearing_venue' => $hearingProcess->hearing_venue,
                    'status' => $hearingProcess->status,
                    'respondent_present' => $hearingProcess->respondent_present,
                    'complainant_present' => $hearingProcess->complainant_present,
                    'hearing_notes' => $hearingProcess->hearing_notes,
                    'next_hearing_date' => $hearingProcess->next_hearing_date,
                ]);
            }
            
            return redirect()->route('case.index')
                ->with('active_tab', 'hearing')
                ->with('edit_id', $id);
        } catch (\Exception $e) {
            if (request()->expectsJson()) {
                return response()->json(['error' => 'Hearing process not found'], 404);
            }
            
            return redirect()->route('case.index')
                ->with('error', 'Hearing process not found.')
                ->with('active_tab', 'hearing');
        }
    }

    /**
     * Update the specified hearing process in storage.
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'case_id' => 'required|exists:cases,id',
            'hearing_date' => 'nullable|date',
            'hearing_time' => 'nullable|string',
            'hearing_officer' => 'nullable|string|max:255',
            'hearing_venue' => 'nullable|string|max:255',
            'status' => 'nullable|string|max:255',
            'respondent_present' => 'nullable|boolean',
            'complainant_present' => 'nullable|boolean',
            'hearing_notes' => 'nullable|string',
            'next_hearing_date' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return redirect()->route('case.index')
                ->withErrors($validator)
                ->withInput()
                ->with('active_tab', 'hearing');
        }

        try {
            $hearingProcess = HearingProcess::findOrFail($id);
            $originalData = $hearingProcess->toArray();
            $hearingProcess->update($request->all());

            $changes = [];
            foreach ($request->all() as $key => $value) {
                if (isset($originalData[$key]) && $originalData[$key] != $value) {
                    $changes[] = ucfirst(str_replace('_', ' ', $key));
                }
            }

            ActivityLogger::logAction(
                'UPDATE',
                'HearingProcess',
                $hearingProcess->case->inspection_id ?? $id,
                'Updated hearing process record',
                [
                    'establishment' => $hearingProcess->case->establishment_name ?? 'Unknown',
                    'fields_changed' => !empty($changes) ? implode(', ', $changes) : 'No changes detected',
                    'change_count' => count($changes)
                ]
            );

            return redirect()->route('case.index')
                ->with('success', 'Hearing process updated successfully.')
                ->with('active_tab', 'hearing');
        } catch (\Exception $e) {
            Log::error('Error updating hearing process ID: ' . $id . ' - ' . $e->getMessage());
            
            return redirect()->route('case.index')
                ->with('error', 'Failed to update hearing process: ' . $e->getMessage())
                ->with('active_tab', 'hearing');
        }
    }

    /**
     * Remove the specified hearing process from storage.
     */
    public function destroy($id)
    {
        try {
            $hearingProcess = HearingProcess::with('case')->findOrFail($id);
            $hearingProcessId = $hearingProcess->case->inspection_id ?? $id;
            $establishment = $hearingProcess->case->establishment_name ?? 'Unknown';

            $hearingProcess->delete();

            ActivityLogger::logAction(
                'DELETE',
                'HearingProcess',
                $hearingProcessId,
                'Deleted hearing process record',
                [
                    'establishment' => $establishment
                ]
            );

            Log::info('Hearing process ID: ' . $id . ' deleted successfully.');

            // Return JSON response for AJAX requests
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Hearing process deleted successfully.'
                ]);
            }

            // Fallback redirect for non-AJAX requests
            return redirect()->route('case.index')
                ->with('success', 'Hearing process deleted successfully.')
                ->with('active_tab', 'hearing');
                
        } catch (\Exception $e) {
            Log::error('Error deleting hearing process ID: ' . $id . ' - ' . $e->getMessage());

            // Return JSON response for AJAX requests
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete hearing process.'
                ], 500);
            }

            // Fallback redirect for non-AJAX requests
            return redirect()->route('case.index')
                ->with('error', 'Failed to delete hearing process: ' . $e->getMessage())
                ->with('active_tab', 'hearing');
        }
    }

    public function inlineUpdate(Request $request, $id)
    {
        Log::info('Hearing process inline update request received', [
            'hearing_id' => $id,
            'request_data' => $request->all(),
            'content_type' => $request->header('Content-Type')
        ]);
        
        try {
            $hearingProcess = HearingProcess::with('case')->findOrFail($id);
            $originalData = $hearingProcess->toArray();
            
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
                    $cleanedData[$key] = is_string($value) ? trim($value) : $value;
                }
            }
            
            Log::info('Cleaned data for validation', ['cleaned_data' => $cleanedData]);
            
            // Updated validation rules with proper field types
            $validator = Validator::make($cleanedData, [
                'date_1st_mc_actual' => 'nullable|date',
                'first_mc_pct' => 'nullable|string|max:255',
                'status_1st_mc' => 'nullable|in:Pending,Ongoing,Completed',
                'date_2nd_last_mc' => 'nullable|date',
                'second_last_mc_pct' => 'nullable|string|max:255',
                'status_2nd_mc' => 'nullable|in:Pending,In Progress,Completed',
                'case_folder_forwarded_to_ro' => 'nullable|string|max:255',
                'complete_case_folder' => 'nullable|in:Y,N',
            ]);

            if ($validator->fails()) {
                Log::warning('Hearing process validation failed', [
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
            
            Log::info('Hearing process update data validated', [
                'validated_data' => $validatedData
            ]);
            
            // Update the hearing process record
            $hearingProcess->update($validatedData);
            Log::info('Hearing process updated successfully');

            // Track changes for activity log
            $changeDetails = [];
            foreach ($validatedData as $field => $newValue) {
                $oldValue = $originalData[$field] ?? null;
                if ($oldValue != $newValue) {
                    $fieldLabel = ucfirst(str_replace('_', ' ', $field));
                    $oldDisplay = $oldValue ?? 'empty';
                    $newDisplay = $newValue ?? 'empty';

                    // Format dates for better readability
                    if (in_array($field, ['date_1st_mc_actual', 'date_2nd_last_mc'])) {
                        $oldDisplay = $oldValue ? Carbon::parse($oldValue)->format('M d, Y') : 'not set';
                        $newDisplay = $newValue ? Carbon::parse($newValue)->format('M d, Y') : 'not set';
                    }

                    $changeDetails[] = "{$fieldLabel}: '{$oldDisplay}' → '{$newDisplay}'";
                }
            }

            // Log the activity if there were changes
            if (!empty($changeDetails)) {
                $logDetails = 'Updated: ' . implode('; ', $changeDetails);
                ActivityLogger::logAction(
                    'UPDATE',
                    'HearingProcess',
                    $hearingProcess->case->inspection_id ?? $id,
                    $logDetails,
                    [
                        'establishment' => $hearingProcess->case->establishment_name ?? 'Unknown',
                        'fields_count' => count($changeDetails),
                        'method' => 'inline_edit'
                    ]
                );
            }

            // Refresh to get updated data
            $hearingProcess->refresh();
            
            // Load the case relationship
            $hearingProcess->load('case');

            // Prepare response data with proper formatting
            $responseData = [
                'inspection_id' => $hearingProcess->case->inspection_id ?? '-',
                'establishment_name' => $hearingProcess->case->establishment_name ?? '-',
                'date_1st_mc_actual' => $hearingProcess->date_1st_mc_actual ? \Carbon\Carbon::parse($hearingProcess->date_1st_mc_actual)->format('Y-m-d') : '-',
                'first_mc_pct' => $hearingProcess->first_mc_pct ?? '-',
                'status_1st_mc' => $hearingProcess->status_1st_mc ?? 'Pending',
                'date_2nd_last_mc' => $hearingProcess->date_2nd_last_mc ? \Carbon\Carbon::parse($hearingProcess->date_2nd_last_mc)->format('Y-m-d') : '-',
                'second_last_mc_pct' => $hearingProcess->second_last_mc_pct ?? '-',
                'status_2nd_mc' => $hearingProcess->status_2nd_mc ?? 'Pending',
                'case_folder_forwarded_to_ro' => $hearingProcess->case_folder_forwarded_to_ro ?? '-',
                'complete_case_folder' => $hearingProcess->complete_case_folder ?? 'N',
            ];

            return response()->json([
                'success' => true,
                'message' => 'Hearing process updated successfully!',
                'data' => $responseData
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('Hearing process not found: ' . $id);
            return response()->json([
                'success' => false,
                'message' => 'Hearing process record not found'
            ], 404);
            
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('Database error in hearing process update: ' . $e->getMessage(), [
                'hearing_id' => $id,
                'sql_state' => $e->errorInfo[0] ?? 'Unknown',
                'error_code' => $e->errorInfo[1] ?? 'Unknown',
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Database error occurred. Please check your data and try again.'
            ], 500);
            
        } catch (\Exception $e) {
            Log::error('Hearing process inline update failed: ' . $e->getMessage(), [
                'hearing_id' => $id,
                'request_data' => $request->all(),
                'error_class' => get_class($e),
                'error_line' => $e->getLine(),
                'error_file' => $e->getFile(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update hearing process: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get hearing process data for AJAX requests.
     */
    public function getHearingProcess($id)
    {
        try {
            $hearingProcess = HearingProcess::with('case')->findOrFail($id);
            
            ActivityLogger::logAction(
                'VIEW',
                'HearingProcess',
                $hearingProcess->case->inspection_id ?? $id,
                'Retrieved hearing process data via API',
                [
                    'establishment' => $hearingProcess->case->establishment_name ?? 'Unknown'
                ]
            );
            
            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $hearingProcess->id,
                    'case_id' => $hearingProcess->case_id,
                    'inspection_id' => $hearingProcess->case->inspection_id ?? '',
                    'establishment_name' => $hearingProcess->case->establishment_name ?? '',
                    'hearing_date' => $hearingProcess->hearing_date,
                    'hearing_time' => $hearingProcess->hearing_time,
                    'hearing_officer' => $hearingProcess->hearing_officer,
                    'hearing_venue' => $hearingProcess->hearing_venue,
                    'status' => $hearingProcess->status,
                    'respondent_present' => $hearingProcess->respondent_present,
                    'complainant_present' => $hearingProcess->complainant_present,
                    'hearing_notes' => $hearingProcess->hearing_notes,
                    'next_hearing_date' => $hearingProcess->next_hearing_date,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Hearing process not found'
            ], 404);
        }
    }
} 