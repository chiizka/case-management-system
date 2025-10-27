<?php

namespace App\Http\Controllers;

use App\Models\AppealsAndResolution;
use App\Models\CaseFile;
use App\Helpers\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AppealsAndResolutionController extends Controller
{
    /**
     * Display a listing of appeals and resolution in the combined case view.
     */
    public function index()
    {
        ActivityLogger::logAction(
            'VIEW',
            'Appeals & Resolution',
            null,
            'Viewed appeals & resolution list page'
        );

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

        DB::beginTransaction();
        try {
            // Create the record
            $appealsAndResolution = AppealsAndResolution::create($request->all());
            $case = $appealsAndResolution->case;

            // Log the action
            ActivityLogger::logAction(
                'CREATE',
                'Appeals & Resolution',
                $case->inspection_id ?? $appealsAndResolution->id,
                'Created new appeals & resolution record',
                [
                    'establishment' => $case->establishment_name ?? 'Unknown',
                    'review_status' => $request->review_ct_cnpc ?? 'Not set',
                    'date_returned' => $request->date_returned_case_mgmt ?? 'Not set'
                ]
            );

            DB::commit();

            return redirect()->route('appeals-and-resolution.index')
                ->with('success', 'Appeals and Resolution record created successfully.')
                ->with('active_tab', 'appeals-and-resolution');
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating appeals & resolution: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            return redirect()->route('appeals-and-resolution.index')
                ->with('error', 'Failed to create appeals & resolution: ' . $e->getMessage())
                ->with('active_tab', 'appeals-and-resolution');
        }
    }

    /**
     * Display the specified appeals and resolution record in the combined view.
     */
    public function show($id)
    {
        try {
            $appealsAndResolution = AppealsAndResolution::with('case')->findOrFail($id);

            ActivityLogger::logAction(
                'VIEW',
                'Appeals & Resolution',
                $appealsAndResolution->case->inspection_id ?? $id,
                'Viewed appeals & resolution details',
                [
                    'establishment' => $appealsAndResolution->case->establishment_name ?? 'Unknown'
                ]
            );
            
            $inspectionId = $appealsAndResolution->case->inspection_id;
            $establishmentName = $appealsAndResolution->case->establishment_name;
            
            $cases = CaseFile::all();
            $appealsAndResolutions = AppealsAndResolution::with('case')->get();
            return view('frontend.case', compact('cases', 'appealsAndResolutions', 'appealsAndResolution'));
        } catch (\Exception $e) {
            return redirect()->route('case.index')
                ->with('error', 'Appeals & Resolution record not found.')
                ->with('active_tab', 'appeals-and-resolution');
        }
    }

    /**
     * Show the form for editing the specified appeals and resolution record in combined view.
     */
    public function edit($id)
    {
        try {
            $appealsAndResolution = AppealsAndResolution::with('case')->findOrFail($id);

            ActivityLogger::logAction(
                'VIEW',
                'Appeals & Resolution',
                $appealsAndResolution->case->inspection_id ?? $id,
                'Opened appeals & resolution record for editing',
                [
                    'establishment' => $appealsAndResolution->case->establishment_name ?? 'Unknown'
                ]
            );

            if (request()->expectsJson()) {
                return response()->json([
                    'id' => $appealsAndResolution->id,
                    'case_id' => $appealsAndResolution->case_id,
                    'inspection_id' => $appealsAndResolution->case->inspection_id ?? '',
                    'establishment_name' => $appealsAndResolution->case->establishment_name ?? '',
                    'date_returned_case_mgmt' => $appealsAndResolution->date_returned_case_mgmt,
                    'review_ct_cnpc' => $appealsAndResolution->review_ct_cnpc,
                    'date_received_drafter_finalization_2nd' => $appealsAndResolution->date_received_drafter_finalization_2nd,
                    'date_returned_case_mgmt_signature_2nd' => $appealsAndResolution->date_returned_case_mgmt_signature_2nd,
                    'date_order_2nd_cnpc' => $appealsAndResolution->date_order_2nd_cnpc,
                    'released_date_2nd_cnpc' => $appealsAndResolution->released_date_2nd_cnpc,
                    'date_forwarded_malsu' => $appealsAndResolution->date_forwarded_malsu,
                    'motion_reconsideration_date' => $appealsAndResolution->motion_reconsideration_date,
                    'date_received_malsu' => $appealsAndResolution->date_received_malsu,
                    'date_resolution_mr' => $appealsAndResolution->date_resolution_mr,
                    'released_date_resolution_mr' => $appealsAndResolution->released_date_resolution_mr,
                    'date_appeal_received_records' => $appealsAndResolution->date_appeal_received_records,
                ]);
            }
            
            $inspectionId = $appealsAndResolution->case->inspection_id;
            $establishmentName = $appealsAndResolution->case->establishment_name;
            
            $cases = CaseFile::all();
            $appealsAndResolutions = AppealsAndResolution::with('case')->get();
            return view('frontend.case', compact('cases', 'appealsAndResolutions', 'appealsAndResolution'));
        } catch (\Exception $e) {
            if (request()->expectsJson()) {
                return response()->json(['error' => 'Appeals & Resolution record not found'], 404);
            }

            return redirect()->route('case.index')
                ->with('error', 'Appeals & Resolution record not found.')
                ->with('active_tab', 'appeals-and-resolution');
        }
    }

    /**
     * Update the specified appeals and resolution record in storage.
     */
    public function update(Request $request, $id)
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

        DB::beginTransaction();
        try {
            // Lock the record to prevent race conditions
            $appealsAndResolution = AppealsAndResolution::lockForUpdate()->findOrFail($id);
            $originalData = $appealsAndResolution->toArray();
            
            // Update the record
            $appealsAndResolution->update($request->all());

            // Track changes
            $changes = [];
            foreach ($request->all() as $key => $value) {
                if (isset($originalData[$key]) && $originalData[$key] != $value) {
                    $changes[] = ucfirst(str_replace('_', ' ', $key));
                }
            }

            // Log the action
            ActivityLogger::logAction(
                'UPDATE',
                'Appeals & Resolution',
                $appealsAndResolution->case->inspection_id ?? $id,
                'Updated appeals & resolution record',
                [
                    'establishment' => $appealsAndResolution->case->establishment_name ?? 'Unknown',
                    'fields_changed' => !empty($changes) ? implode(', ', $changes) : 'No changes detected',
                    'change_count' => count($changes)
                ]
            );

            DB::commit();

            return redirect()->route('appeals-and-resolution.index')
                ->with('success', 'Appeals and Resolution record updated successfully.')
                ->with('active_tab', 'appeals-and-resolution');
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating appeals & resolution ID: ' . $id . ' - ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            return redirect()->route('appeals-and-resolution.index')
                ->with('error', 'Failed to update appeals & resolution: ' . $e->getMessage())
                ->with('active_tab', 'appeals-and-resolution');
        }
    }

    /**
     * Remove the specified appeals and resolution record from storage.
     */
    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            // Lock the record
            $appealsAndResolution = AppealsAndResolution::lockForUpdate()->with('case')->findOrFail($id);
            
            // Store info for logging before deletion
            $appealsAndResolutionId = $appealsAndResolution->case->inspection_id ?? $id;
            $establishment = $appealsAndResolution->case->establishment_name ?? 'Unknown';

            // Delete the record
            $appealsAndResolution->delete();

            // Log the action
            ActivityLogger::logAction(
                'DELETE',
                'Appeals & Resolution',
                $appealsAndResolutionId,
                'Deleted appeals & resolution record',
                [
                    'establishment' => $establishment
                ]
            );

            DB::commit();

            Log::info('Appeals and Resolution ID: ' . $id . ' deleted successfully.');

            // Return JSON response for AJAX requests
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Appeals and resolution deleted successfully.'
                ]);
            }

            // Fallback redirect for non-AJAX requests
            return redirect()->route('case.index')
                ->with('success', 'Appeals and Resolution record deleted successfully.')
                ->with('active_tab', 'appeals-and-resolution');
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting Appeals and Resolution ID: ' . $id . ' - ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            // Return JSON response for AJAX requests
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete appeals and resolution.'
                ], 500);
            }

            // Fallback redirect for non-AJAX requests
            return redirect()->route('case.index')
                ->with('error', 'Failed to delete Appeals and Resolution record: ' . $e->getMessage())
                ->with('active_tab', 'appeals-and-resolution');
        }
    }

    /**
     * Inline update handler for AJAX.
     */
    public function inlineUpdate(Request $request, $id)
    {
        Log::info('Appeals and Resolution inline update received', [
            'id' => $id, 
            'data' => $request->all()
        ]);

        DB::beginTransaction();
        try {
            // Lock the record
            $appealsAndResolution = AppealsAndResolution::lockForUpdate()->with('case')->findOrFail($id);
            $originalData = $appealsAndResolution->toArray();

            // Clean input data
            $inputData = $request->all();
            foreach ($inputData as $key => $value) {
                $inputData[$key] = ($value === '' || $value === '-') ? null : $value;
            }

            // Validate data
            $validator = Validator::make($inputData, [
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
                DB::rollBack();
                Log::warning('Appeals & Resolution validation failed', [
                    'errors' => $validator->errors(),
                    'data' => $inputData
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed: ' . $validator->errors()->first(),
                    'errors' => $validator->errors()
                ], 422);
            }

            $validatedData = $validator->validated();
            
            // Update the record
            $appealsAndResolution->update($validatedData);
            $appealsAndResolution->refresh()->load('case');

            // Track changes for activity log
            $changeDetails = [];
            foreach ($validatedData as $field => $newValue) {
                $oldValue = $originalData[$field] ?? null;
                if ($oldValue != $newValue) {
                    $fieldLabel = ucfirst(str_replace('_', ' ', $field));
                    $oldDisplay = $oldValue ?? 'empty';
                    $newDisplay = $newValue ?? 'empty';

                    // Format dates for better readability
                    if (strpos($field, 'date') === 0 || strpos($field, '_date') !== false) {
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
                    'Appeals & Resolution',
                    $appealsAndResolution->case->inspection_id ?? $id,
                    $logDetails,
                    [
                        'establishment' => $appealsAndResolution->case->establishment_name ?? 'Unknown',
                        'fields_count' => count($changeDetails),
                        'method' => 'inline_edit'
                    ]
                );
            } else {
                ActivityLogger::logAction(
                    'UPDATE',
                    'Appeals & Resolution',
                    $appealsAndResolution->case->inspection_id ?? $id,
                    'Attempted update with no changes',
                    [
                        'establishment' => $appealsAndResolution->case->establishment_name ?? 'Unknown',
                        'method' => 'inline_edit'
                    ]
                );
            }

            DB::commit();

            // Format response with all fields properly formatted including case fields
            $responseData = [
                'inspection_id' => $appealsAndResolution->case->inspection_id ?? '-',
                'case_no' => $appealsAndResolution->case->case_no ?? '-',
                'establishment_name' => $appealsAndResolution->case->establishment_name ?? '-',
                'date_returned_case_mgmt' => $appealsAndResolution->date_returned_case_mgmt ? Carbon::parse($appealsAndResolution->date_returned_case_mgmt)->format('Y-m-d') : '-',
                'review_ct_cnpc' => $appealsAndResolution->review_ct_cnpc ?? '-',
                'date_received_drafter_finalization_2nd' => $appealsAndResolution->date_received_drafter_finalization_2nd ? Carbon::parse($appealsAndResolution->date_received_drafter_finalization_2nd)->format('Y-m-d') : '-',
                'date_returned_case_mgmt_signature_2nd' => $appealsAndResolution->date_returned_case_mgmt_signature_2nd ? Carbon::parse($appealsAndResolution->date_returned_case_mgmt_signature_2nd)->format('Y-m-d') : '-',
                'date_order_2nd_cnpc' => $appealsAndResolution->date_order_2nd_cnpc ? Carbon::parse($appealsAndResolution->date_order_2nd_cnpc)->format('Y-m-d') : '-',
                'released_date_2nd_cnpc' => $appealsAndResolution->released_date_2nd_cnpc ? Carbon::parse($appealsAndResolution->released_date_2nd_cnpc)->format('Y-m-d') : '-',
                'date_forwarded_malsu' => $appealsAndResolution->date_forwarded_malsu ? Carbon::parse($appealsAndResolution->date_forwarded_malsu)->format('Y-m-d') : '-',
                'motion_reconsideration_date' => $appealsAndResolution->motion_reconsideration_date ? Carbon::parse($appealsAndResolution->motion_reconsideration_date)->format('Y-m-d') : '-',
                'date_received_malsu' => $appealsAndResolution->date_received_malsu ? Carbon::parse($appealsAndResolution->date_received_malsu)->format('Y-m-d') : '-',
                'date_resolution_mr' => $appealsAndResolution->date_resolution_mr ? Carbon::parse($appealsAndResolution->date_resolution_mr)->format('Y-m-d') : '-',
                'released_date_resolution_mr' => $appealsAndResolution->released_date_resolution_mr ? Carbon::parse($appealsAndResolution->released_date_resolution_mr)->format('Y-m-d') : '-',
                'date_appeal_received_records' => $appealsAndResolution->date_appeal_received_records ? Carbon::parse($appealsAndResolution->date_appeal_received_records)->format('Y-m-d') : '-',
            ];

            return response()->json([
                'success' => true,
                'message' => 'Appeals & Resolution updated successfully!',
                'data' => $responseData
            ]);
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            Log::error('Appeals & Resolution not found: ' . $id);
            return response()->json([
                'success' => false,
                'message' => 'Appeals & Resolution record not found'
            ], 404);
            
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            Log::error('Database error in appeals & resolution update: ' . $e->getMessage(), [
                'appeals_resolution_id' => $id,
                'sql_state' => $e->errorInfo[0] ?? 'Unknown',
                'error_code' => $e->errorInfo[1] ?? 'Unknown',
                'stack_trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Database error occurred. Please check your data and try again.'
            ], 500);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Appeals & Resolution inline update failed: ' . $e->getMessage(), [
                'appeals_resolution_id' => $id,
                'request_data' => $request->all(),
                'error_class' => get_class($e),
                'error_line' => $e->getLine(),
                'error_file' => $e->getFile(),
                'stack_trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update appeals & resolution: ' . $e->getMessage()
            ], 500);
        }
    }
}