<?php

namespace App\Http\Controllers;

use App\Models\ReviewAndDrafting;
use App\Models\CaseFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class ReviewAndDraftingController extends Controller
{
    /**
     * Display a listing of review and drafting processes in the combined case view.
     */
    public function index()
    {
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
            'po_pct' => 'nullable|string|max:255',
            'aging_po_pct' => 'nullable|string|max:255',
            'status_po_pct' => 'nullable|string|max:255',
            'date_received_from_po' => 'nullable|date',
            'reviewer_drafter' => 'nullable|string|max:255',
            'date_received_by_reviewer' => 'nullable|date',
            'date_returned_from_drafter' => 'nullable|date',
            'aging_10_days_tssd' => 'nullable|string|max:255',
            'status_reviewer_drafter' => 'nullable|string|max:255',
            'draft_order_tssd_reviewer' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->route('case.index')
                ->withErrors($validator)
                ->withInput()
                ->with('active_tab', 'review-drafting');
        }

        try {
            ReviewAndDrafting::create($request->all());
            
            return redirect()->route('case.index')
                ->with('success', 'Review and drafting process created successfully.')
                ->with('active_tab', 'review-drafting');
        } catch (\Exception $e) {
            Log::error('Error creating review and drafting process: ' . $e->getMessage());
            
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
            'po_pct' => 'nullable|string|max:255',
            'aging_po_pct' => 'nullable|string|max:255',
            'status_po_pct' => 'nullable|string|max:255',
            'date_received_from_po' => 'nullable|date',
            'reviewer_drafter' => 'nullable|string|max:255',
            'date_received_by_reviewer' => 'nullable|date',
            'date_returned_from_drafter' => 'nullable|date',
            'aging_10_days_tssd' => 'nullable|string|max:255',
            'status_reviewer_drafter' => 'nullable|string|max:255',
            'draft_order_tssd_reviewer' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->route('case.index')
                ->withErrors($validator)
                ->withInput()
                ->with('active_tab', 'review-drafting');
        }

        try {
            $reviewAndDrafting = ReviewAndDrafting::findOrFail($id);
            $reviewAndDrafting->update($request->all());

            return redirect()->route('case.index')
                ->with('success', 'Review and drafting process updated successfully.')
                ->with('active_tab', 'review-drafting');
        } catch (\Exception $e) {
            Log::error('Error updating review and drafting process ID: ' . $id . ' - ' . $e->getMessage());
            
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
        try {
            $reviewAndDrafting = ReviewAndDrafting::findOrFail($id);
            $reviewAndDrafting->delete();
            Log::info('Review and drafting process ID: ' . $id . ' deleted successfully.');

            return redirect()->route('case.index')
                ->with('success', 'Review and drafting process deleted successfully.')
                ->with('active_tab', 'review-drafting');
        } catch (\Exception $e) {
            Log::error('Error deleting review and drafting process ID: ' . $id . ' - ' . $e->getMessage());
            
            return redirect()->route('case.index')
                ->with('error', 'Failed to delete review and drafting process: ' . $e->getMessage())
                ->with('active_tab', 'review-drafting');
        }
    }

    public function inlineUpdate(Request $request, $id)
    {
        Log::info('Review and drafting inline update request received', [
            'review_drafting_id' => $id,
            'request_data' => $request->all(),
            'content_type' => $request->header('Content-Type')
        ]);
        
        try {
            $reviewAndDrafting = ReviewAndDrafting::findOrFail($id);
            
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
            
            // Validation rules
            $validator = Validator::make($cleanedData, [
                'draft_order_type' => 'nullable|string|max:255',
                'applicable_draft_order' => 'nullable|in:Y,N',
                'po_pct' => 'nullable|string|max:255',
                'aging_po_pct' => 'nullable|string|max:255',
                'status_po_pct' => 'nullable|in:Pending,In Progress,Completed',
                'date_received_from_po' => 'nullable|date',
                'reviewer_drafter' => 'nullable|string|max:255',
                'date_received_by_reviewer' => 'nullable|date',
                'date_returned_from_drafter' => 'nullable|date',
                'aging_10_days_tssd' => 'nullable|string|max:255',
                'status_reviewer_drafter' => 'nullable|in:Pending,Ongoing,Completed,Approved,Returned,Overdue',
                'draft_order_tssd_reviewer' => 'nullable|string|max:255',
            ]);

            if ($validator->fails()) {
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
                'date_received_from_po' => $reviewAndDrafting->date_received_from_po ? \Carbon\Carbon::parse($reviewAndDrafting->date_received_from_po)->format('Y-m-d') : '-',
                'reviewer_drafter' => $reviewAndDrafting->reviewer_drafter ?? '-',
                'date_received_by_reviewer' => $reviewAndDrafting->date_received_by_reviewer ? \Carbon\Carbon::parse($reviewAndDrafting->date_received_by_reviewer)->format('Y-m-d') : '-',
                'date_returned_from_drafter' => $reviewAndDrafting->date_returned_from_drafter ? \Carbon\Carbon::parse($reviewAndDrafting->date_returned_from_drafter)->format('Y-m-d') : '-',
                'aging_10_days_tssd' => $reviewAndDrafting->aging_10_days_tssd ?? '-',
                'status_reviewer_drafter' => $reviewAndDrafting->status_reviewer_drafter ?? 'Pending',
                'draft_order_tssd_reviewer' => $reviewAndDrafting->draft_order_tssd_reviewer ?? '-',
            ];

            return response()->json([
                'success' => true,
                'message' => 'Review and drafting process updated successfully!',
                'data' => $responseData
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('Review and drafting process not found: ' . $id);
            return response()->json([
                'success' => false,
                'message' => 'Review and drafting process record not found'
            ], 404);
            
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('Database error in review and drafting update: ' . $e->getMessage(), [
                'review_drafting_id' => $id,
                'sql_state' => $e->errorInfo[0] ?? 'Unknown',
                'error_code' => $e->errorInfo[1] ?? 'Unknown',
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Database error occurred. Please check your data and try again.'
            ], 500);
            
        } catch (\Exception $e) {
            Log::error('Review and drafting inline update failed: ' . $e->getMessage(), [
                'review_drafting_id' => $id,
                'request_data' => $request->all(),
                'error_class' => get_class($e),
                'error_line' => $e->getLine(),
                'error_file' => $e->getFile(),
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