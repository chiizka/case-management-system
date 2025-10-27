<?php

namespace App\Http\Controllers;

use App\Models\ReviewAndDrafting;
use App\Models\CaseFile;
use App\Helpers\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReviewAndDraftingController extends Controller
{
    /**
     * Display a listing of review and drafting processes in the combined case view.
     */
    public function index()
    {
        ActivityLogger::logAction(
            'VIEW',
            'ReviewAndDrafting',
            null,
            'Viewed review and drafting list page'
        );

        // Redirect to unified case view instead of loading data here
        return redirect()->route('case.index')
            ->with('active_tab', 'review-drafting');
    }

    /**
     * Show the form for creating a new review and drafting process (combined view).
     */
    public function create()
    {
        return redirect()->route('case.index')
            ->with('active_tab', 'review-drafting');
    }

    /**
     * Store a newly created review and drafting process in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'case_id' => 'required|exists:cases,id',
            'draft_order_type' => 'nullable|string|max:255',
            'applicable_draft_order' => 'nullable|in:Y,N',
            'po_pct' => 'nullable|integer',
            'aging_po_pct' => 'nullable|integer',
            'status_po_pct' => 'nullable|in:Pending,Ongoing,Overdue,Completed',
            'date_received_from_po' => 'nullable|date',
            'reviewer_drafter' => 'nullable|string|max:255',
            'date_received_by_reviewer' => 'nullable|date',
            'date_returned_from_drafter' => 'nullable|date',
            'aging_10_days_tssd' => 'nullable|integer',
            'status_reviewer_drafter' => 'nullable|in:Pending,Ongoing,Returned,Approved,Overdue',
            'draft_order_tssd_reviewer' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->route('case.index')
                ->withErrors($validator)
                ->withInput()
                ->with('active_tab', 'review-drafting');
        }

        DB::beginTransaction();
        try {
            $reviewAndDrafting = ReviewAndDrafting::create($request->all());
            $case = $reviewAndDrafting->case;

            ActivityLogger::logAction(
                'CREATE',
                'ReviewAndDrafting',
                $case->inspection_id ?? $reviewAndDrafting->id,
                'Created new review and drafting process record',
                [
                    'establishment' => $case->establishment_name ?? 'Unknown',
                    'draft_order_type' => $request->draft_order_type ?? 'Not set',
                    'reviewer_drafter' => $request->reviewer_drafter ?? 'Not assigned',
                    'status_po_pct' => $request->status_po_pct ?? 'Pending'
                ]
            );

            DB::commit();
            
            return redirect()->route('case.index')
                ->with('success', 'Review and drafting process created successfully.')
                ->with('active_tab', 'review-drafting');
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating review and drafting process: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return redirect()->route('case.index')
                ->with('error', 'Failed to create review and drafting process: ' . $e->getMessage())
                ->with('active_tab', 'review-drafting');
        }
    }

    /**
     * Display the specified review and drafting process in the combined view.
     */
    public function show($id)
    {
        try {
            $reviewAndDrafting = ReviewAndDrafting::with('case')->findOrFail($id);
            
            ActivityLogger::logAction(
                'VIEW',
                'ReviewAndDrafting',
                $reviewAndDrafting->case->inspection_id ?? $id,
                'Viewed review and drafting process details',
                [
                    'establishment' => $reviewAndDrafting->case->establishment_name ?? 'Unknown'
                ]
            );
            
            return redirect()->route('case.index')
                ->with('active_tab', 'review-drafting')
                ->with('highlighted_id', $id);
        } catch (\Exception $e) {
            return redirect()->route('case.index')
                ->with('error', 'Review and drafting process not found.')
                ->with('active_tab', 'review-drafting');
        }
    }

    /**
     * Show the form for editing the specified review and drafting process.
     */
    public function edit($id)
    {
        try {
            $reviewAndDrafting = ReviewAndDrafting::with('case')->findOrFail($id);
            
            ActivityLogger::logAction(
                'VIEW',
                'ReviewAndDrafting',
                $reviewAndDrafting->case->inspection_id ?? $id,
                'Opened review and drafting process record for editing',
                [
                    'establishment' => $reviewAndDrafting->case->establishment_name ?? 'Unknown'
                ]
            );
            
            // Return JSON for AJAX requests
            if (request()->expectsJson()) {
                return response()->json([
                    'id' => $reviewAndDrafting->id,
                    'case_id' => $reviewAndDrafting->case_id,
                    'inspection_id' => $reviewAndDrafting->case->inspection_id ?? '',
                    'establishment_name' => $reviewAndDrafting->case->establishment_name ?? '',
                    'draft_order_type' => $reviewAndDrafting->draft_order_type,
                    'applicable_draft_order' => $reviewAndDrafting->applicable_draft_order,
                    'po_pct' => $reviewAndDrafting->po_pct,
                    'aging_po_pct' => $reviewAndDrafting->aging_po_pct,
                    'status_po_pct' => $reviewAndDrafting->status_po_pct,
                    'date_received_from_po' => $reviewAndDrafting->date_received_from_po,
                    'reviewer_drafter' => $reviewAndDrafting->reviewer_drafter,
                    'date_received_by_reviewer' => $reviewAndDrafting->date_received_by_reviewer,
                    'date_returned_from_drafter' => $reviewAndDrafting->date_returned_from_drafter,
                    'aging_10_days_tssd' => $reviewAndDrafting->aging_10_days_tssd,
                    'status_reviewer_drafter' => $reviewAndDrafting->status_reviewer_drafter,
                    'draft_order_tssd_reviewer' => $reviewAndDrafting->draft_order_tssd_reviewer,
                ]);
            }
            
            return redirect()->route('case.index')
                ->with('active_tab', 'review-drafting')
                ->with('edit_id', $id);
        } catch (\Exception $e) {
            if (request()->expectsJson()) {
                return response()->json(['error' => 'Review and drafting process not found'], 404);
            }
            
            return redirect()->route('case.index')
                ->with('error', 'Review and drafting process not found.')
                ->with('active_tab', 'review-drafting');
        }
    }

    /**
     * Update the specified review and drafting process in storage.
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'case_id' => 'required|exists:cases,id',
            'draft_order_type' => 'nullable|string|max:255',
            'applicable_draft_order' => 'nullable|in:Y,N',
            'po_pct' => 'nullable|integer',
            'aging_po_pct' => 'nullable|integer',
            'status_po_pct' => 'nullable|in:Pending,Ongoing,Overdue,Completed',
            'date_received_from_po' => 'nullable|date',
            'reviewer_drafter' => 'nullable|string|max:255',
            'date_received_by_reviewer' => 'nullable|date',
            'date_returned_from_drafter' => 'nullable|date',
            'aging_10_days_tssd' => 'nullable|integer',
            'status_reviewer_drafter' => 'nullable|in:Pending,Ongoing,Returned,Approved,Overdue',
            'draft_order_tssd_reviewer' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->route('case.index')
                ->withErrors($validator)
                ->withInput()
                ->with('active_tab', 'review-drafting');
        }

        DB::beginTransaction();
        try {
            // Lock the record to prevent race conditions
            $reviewAndDrafting = ReviewAndDrafting::lockForUpdate()->findOrFail($id);
            $originalData = $reviewAndDrafting->toArray();
            
            // Update the record
            $reviewAndDrafting->update($request->all());

            // Track changes
            $changes = [];
            foreach ($request->all() as $key => $value) {
                if (isset($originalData[$key]) && $originalData[$key] != $value) {
                    $changes[] = ucfirst(str_replace('_', ' ', $key));
                }
            }

            ActivityLogger::logAction(
                'UPDATE',
                'ReviewAndDrafting',
                $reviewAndDrafting->case->inspection_id ?? $id,
                'Updated review and drafting process record',
                [
                    'establishment' => $reviewAndDrafting->case->establishment_name ?? 'Unknown',
                    'fields_changed' => !empty($changes) ? implode(', ', $changes) : 'No changes detected',
                    'change_count' => count($changes)
                ]
            );

            DB::commit();

            return redirect()->route('case.index')
                ->with('success', 'Review and drafting process updated successfully.')
                ->with('active_tab', 'review-drafting');
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating review and drafting process ID: ' . $id . ' - ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return redirect()->route('case.index')
                ->with('error', 'Failed to update review and drafting process: ' . $e->getMessage())
                ->with('active_tab', 'review-drafting');
        }
    }

    /**
     * Remove the specified review and drafting process from storage.
     */
    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            // Lock the record
            $reviewAndDrafting = ReviewAndDrafting::lockForUpdate()->with('case')->findOrFail($id);
            
            // Store info for logging before deletion
            $reviewDraftingId = $reviewAndDrafting->case->inspection_id ?? $id;
            $establishment = $reviewAndDrafting->case->establishment_name ?? 'Unknown';

            // Delete the record
            $reviewAndDrafting->delete();

            ActivityLogger::logAction(
                'DELETE',
                'ReviewAndDrafting',
                $reviewDraftingId,
                'Deleted review and drafting process record',
                [
                    'establishment' => $establishment
                ]
            );

            DB::commit();

            Log::info('Review and drafting process ID: ' . $id . ' deleted successfully.');

            // Return JSON response for AJAX requests
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Review and drafting process deleted successfully.'
                ]);
            }

            // Fallback redirect for non-AJAX requests
            return redirect()->route('case.index')
                ->with('success', 'Review and drafting process deleted successfully.')
                ->with('active_tab', 'review-drafting');
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting review and drafting process ID: ' . $id . ' - ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            // Return JSON response for AJAX requests
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete review and drafting process.'
                ], 500);
            }

            // Fallback redirect for non-AJAX requests
            return redirect()->route('case.index')
                ->with('error', 'Failed to delete review and drafting process: ' . $e->getMessage())
                ->with('active_tab', 'review-drafting');
        }
    }

    /**
     * AJAX inline update method for review and drafting records
     */
    public function inlineUpdate(Request $request, $id)
    {
        Log::info('Review and drafting inline update request received', [
            'review_drafting_id' => $id,
            'request_data' => $request->all(),
            'content_type' => $request->header('Content-Type'),
            'method' => $request->method(),
            'url' => $request->url()
        ]);
        
        DB::beginTransaction();
        try {
            // Lock the review and drafting row
            $reviewAndDrafting = ReviewAndDrafting::lockForUpdate()->with('case')->findOrFail($id);
            $originalData = $reviewAndDrafting->toArray();
            
            // Get all input data
            $inputData = $request->all();
            
            // Remove readonly fields (these come from the case relationship)
            unset($inputData['inspection_id']);
            unset($inputData['establishment_name']);
            
            // Clean and convert data types
            $cleanedData = [];
            foreach ($inputData as $key => $value) {
                if ($value === '' || $value === '-') {
                    $cleanedData[$key] = null;
                } else {
                    // Handle integer fields
                    if (in_array($key, ['po_pct', 'aging_po_pct', 'aging_10_days_tssd'])) {
                        $cleanedData[$key] = is_numeric($value) ? (int)$value : null;
                    } else {
                        $cleanedData[$key] = is_string($value) ? trim($value) : $value;
                    }
                }
            }
            
            Log::info('Cleaned data for validation', ['cleaned_data' => $cleanedData]);
            
            // Validation rules matching your migration exactly
            $validator = Validator::make($cleanedData, [
                'draft_order_type' => 'nullable|string|max:255',
                'applicable_draft_order' => 'nullable|in:Y,N',
                'po_pct' => 'nullable|integer',
                'aging_po_pct' => 'nullable|integer',
                'status_po_pct' => 'nullable|in:Pending,Ongoing,Overdue,Completed',
                'date_received_from_po' => 'nullable|date',
                'reviewer_drafter' => 'nullable|string|max:255',
                'date_received_by_reviewer' => 'nullable|date',
                'date_returned_from_drafter' => 'nullable|date',
                'aging_10_days_tssd' => 'nullable|integer',
                'status_reviewer_drafter' => 'nullable|in:Pending,Ongoing,Returned,Approved,Overdue',
                'draft_order_tssd_reviewer' => 'nullable|string|max:255',
            ]);

            if ($validator->fails()) {
                DB::rollBack();
                Log::warning('Review and drafting validation failed', [
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
            
            Log::info('Review and drafting update data validated', [
                'validated_data' => $validatedData
            ]);
            
            // Update the review and drafting record
            $reviewAndDrafting->update($validatedData);
            Log::info('Review and drafting updated successfully');

            // Track changes for activity log
            $changeDetails = [];
            foreach ($validatedData as $field => $newValue) {
                $oldValue = $originalData[$field] ?? null;
                if ($oldValue != $newValue) {
                    $fieldLabel = ucfirst(str_replace('_', ' ', $field));
                    $oldDisplay = $oldValue ?? 'empty';
                    $newDisplay = $newValue ?? 'empty';

                    // Format dates for better readability
                    if (in_array($field, ['date_received_from_po', 'date_received_by_reviewer', 'date_returned_from_drafter'])) {
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
                    'ReviewAndDrafting',
                    $reviewAndDrafting->case->inspection_id ?? $id,
                    $logDetails,
                    [
                        'establishment' => $reviewAndDrafting->case->establishment_name ?? 'Unknown',
                        'fields_count' => count($changeDetails),
                        'method' => 'inline_edit'
                    ]
                );
            } else {
                ActivityLogger::logAction(
                    'UPDATE',
                    'ReviewAndDrafting',
                    $reviewAndDrafting->case->inspection_id ?? $id,
                    'Attempted update with no changes',
                    [
                        'establishment' => $reviewAndDrafting->case->establishment_name ?? 'Unknown',
                        'method' => 'inline_edit'
                    ]
                );
            }

            // Refresh to get updated data
            $reviewAndDrafting->refresh();
            
            // Load the case relationship
            $reviewAndDrafting->load('case');

            // Prepare response data with proper formatting
            $responseData = [
                'inspection_id' => $reviewAndDrafting->case->inspection_id ?? '-',
                'establishment_name' => $reviewAndDrafting->case->establishment_name ?? '-',
                'draft_order_type' => $reviewAndDrafting->draft_order_type ?? '-',
                'applicable_draft_order' => $reviewAndDrafting->applicable_draft_order ?? 'N',
                'po_pct' => $reviewAndDrafting->po_pct ?? '-',
                'aging_po_pct' => $reviewAndDrafting->aging_po_pct ?? '-',
                'status_po_pct' => $reviewAndDrafting->status_po_pct ?? 'Pending',
                'date_received_from_po' => $reviewAndDrafting->date_received_from_po ? Carbon::parse($reviewAndDrafting->date_received_from_po)->format('Y-m-d') : '-',
                'reviewer_drafter' => $reviewAndDrafting->reviewer_drafter ?? '-',
                'date_received_by_reviewer' => $reviewAndDrafting->date_received_by_reviewer ? Carbon::parse($reviewAndDrafting->date_received_by_reviewer)->format('Y-m-d') : '-',
                'date_returned_from_drafter' => $reviewAndDrafting->date_returned_from_drafter ? Carbon::parse($reviewAndDrafting->date_returned_from_drafter)->format('Y-m-d') : '-',
                'aging_10_days_tssd' => $reviewAndDrafting->aging_10_days_tssd ?? '-',
                'status_reviewer_drafter' => $reviewAndDrafting->status_reviewer_drafter ?? 'Pending',
                'draft_order_tssd_reviewer' => $reviewAndDrafting->draft_order_tssd_reviewer ?? '-',
            ];

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Review and drafting process updated successfully!',
                'data' => $responseData
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            Log::error('Review and drafting process not found: ' . $id);
            return response()->json([
                'success' => false,
                'message' => 'Review and drafting process record not found'
            ], 404);
            
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            Log::error('Database error in review and drafting update: ' . $e->getMessage(), [
                'review_drafting_id' => $id,
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
            Log::error('Review and drafting inline update failed: ' . $e->getMessage(), [
                'review_drafting_id' => $id,
                'request_data' => $request->all(),
                'error_class' => get_class($e),
                'error_line' => $e->getLine(),
                'error_file' => $e->getFile(),
                'stack_trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update review and drafting process: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get review and drafting process data for AJAX requests.
     */
    public function getReviewAndDrafting($id)
    {
        try {
            $reviewAndDrafting = ReviewAndDrafting::with('case')->findOrFail($id);
            
            ActivityLogger::logAction(
                'VIEW',
                'ReviewAndDrafting',
                $reviewAndDrafting->case->inspection_id ?? $id,
                'Retrieved review and drafting process data via API',
                [
                    'establishment' => $reviewAndDrafting->case->establishment_name ?? 'Unknown'
                ]
            );
            
            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $reviewAndDrafting->id,
                    'case_id' => $reviewAndDrafting->case_id,
                    'inspection_id' => $reviewAndDrafting->case->inspection_id ?? '',
                    'establishment_name' => $reviewAndDrafting->case->establishment_name ?? '',
                    'draft_order_type' => $reviewAndDrafting->draft_order_type,
                    'applicable_draft_order' => $reviewAndDrafting->applicable_draft_order,
                    'po_pct' => $reviewAndDrafting->po_pct,
                    'aging_po_pct' => $reviewAndDrafting->aging_po_pct,
                    'status_po_pct' => $reviewAndDrafting->status_po_pct,
                    'date_received_from_po' => $reviewAndDrafting->date_received_from_po,
                    'reviewer_drafter' => $reviewAndDrafting->reviewer_drafter,
                    'date_received_by_reviewer' => $reviewAndDrafting->date_received_by_reviewer,
                    'date_returned_from_drafter' => $reviewAndDrafting->date_returned_from_drafter,
                    'aging_10_days_tssd' => $reviewAndDrafting->aging_10_days_tssd,
                    'status_reviewer_drafter' => $reviewAndDrafting->status_reviewer_drafter,
                    'draft_order_tssd_reviewer' => $reviewAndDrafting->draft_order_tssd_reviewer,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Review and drafting process not found'
            ], 404);
        }
    }
}