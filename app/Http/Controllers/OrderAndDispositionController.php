<?php

namespace App\Http\Controllers;

use App\Models\OrderAndDisposition;
use App\Models\CaseFile;
use App\Helpers\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class OrderAndDispositionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        ActivityLogger::logAction(
            'VIEW',
            'OrderAndDisposition',
            null,
            'Viewed order and disposition list page'
        );

        $query = OrderAndDisposition::with('case');

        // Filter by case if provided
        if ($request->has('case_id')) {
            $query->where('case_id', $request->case_id);
        }

        // Filter by status if provided
        if ($request->has('status')) {
            $query->where('status_finalization', $request->status);
        }

        // Pagination
        $ordersAndDispositions = $query->paginate(15);

        return response()->json($ordersAndDispositions);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'case_id' => 'required|exists:cases,id',
            'status_finalization' => 'nullable|string|max:255',
            'date_signed_mis' => 'nullable|date',
            'status_pct' => 'nullable|string|max:255',
            'reference_date_pct' => 'nullable|date',
            'disposition_mis' => 'nullable|string|max:255',
            'disposition_actual' => 'nullable|string|max:255',
            'findings_to_comply' => 'nullable|string',
            'date_of_order_actual' => 'nullable|date',
            'released_date_actual' => 'nullable|date',
        ]);

        DB::beginTransaction();
        try {
            $orderAndDisposition = OrderAndDisposition::create($validated);
            $case = $orderAndDisposition->case;

            ActivityLogger::logAction(
                'CREATE',
                'OrderAndDisposition',
                $case->inspection_id ?? $orderAndDisposition->id,
                'Created new order and disposition record',
                [
                    'establishment' => $case->establishment_name ?? 'Unknown',
                    'status_finalization' => $request->status_finalization ?? 'Not set',
                    'disposition_actual' => $request->disposition_actual ?? 'Not set',
                ]
            );

            DB::commit();

            return response()->json([
                'message' => 'Order and Disposition created successfully',
                'data' => $orderAndDisposition->load('case')
            ], 201);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating order and disposition: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'message' => 'Failed to create order and disposition',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(OrderAndDisposition $orderAndDisposition)
    {
        ActivityLogger::logAction(
            'VIEW',
            'OrderAndDisposition',
            $orderAndDisposition->case->inspection_id ?? $orderAndDisposition->id,
            'Viewed order and disposition details',
            [
                'establishment' => $orderAndDisposition->case->establishment_name ?? 'Unknown'
            ]
        );

        return response()->json([
            'data' => $orderAndDisposition->load('case')
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, OrderAndDisposition $orderAndDisposition)
    {
        $validated = $request->validate([
            'case_id' => 'sometimes|exists:cases,id',
            'status_finalization' => 'nullable|string|max:255',
            'date_signed_mis' => 'nullable|date',
            'status_pct' => 'nullable|string|max:255',
            'reference_date_pct' => 'nullable|date',
            'disposition_mis' => 'nullable|string|max:255',
            'disposition_actual' => 'nullable|string|max:255',
            'findings_to_comply' => 'nullable|string',
            'date_of_order_actual' => 'nullable|date',
            'released_date_actual' => 'nullable|date',
        ]);

        DB::beginTransaction();
        try {
            // Lock the record to prevent race conditions
            $orderAndDisposition = OrderAndDisposition::lockForUpdate()->findOrFail($orderAndDisposition->id);
            $originalData = $orderAndDisposition->toArray();
            
            // Update the record
            $orderAndDisposition->update($validated);

            // Track changes
            $changes = [];
            foreach ($validated as $key => $value) {
                if (isset($originalData[$key]) && $originalData[$key] != $value) {
                    $changes[] = ucfirst(str_replace('_', ' ', $key));
                }
            }

            ActivityLogger::logAction(
                'UPDATE',
                'OrderAndDisposition',
                $orderAndDisposition->case->inspection_id ?? $orderAndDisposition->id,
                'Updated order and disposition record',
                [
                    'establishment' => $orderAndDisposition->case->establishment_name ?? 'Unknown',
                    'fields_changed' => !empty($changes) ? implode(', ', $changes) : 'No changes detected',
                    'change_count' => count($changes)
                ]
            );

            DB::commit();

            return response()->json([
                'message' => 'Order and Disposition updated successfully',
                'data' => $orderAndDisposition->load('case')
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating order and disposition ID: ' . $orderAndDisposition->id . ' - ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'message' => 'Failed to update order and disposition',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            // Lock the record
            $orderAndDisposition = OrderAndDisposition::lockForUpdate()->with('case')->findOrFail($id);
            
            // Store info for logging before deletion
            $orderId = $orderAndDisposition->case->inspection_id ?? $id;
            $establishment = $orderAndDisposition->case->establishment_name ?? 'Unknown';

            // Delete the record
            $orderAndDisposition->delete();

            ActivityLogger::logAction(
                'DELETE',
                'OrderAndDisposition',
                $orderId,
                'Deleted order and disposition record',
                [
                    'establishment' => $establishment
                ]
            );

            DB::commit();

            Log::info('Order and disposition ID: ' . $id . ' deleted successfully.');

            // Return JSON response for AJAX requests
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Order and disposition deleted successfully.'
                ]);
            }

            // Fallback redirect for non-AJAX requests
            return redirect()->route('case.index')
                ->with('success', 'Order and disposition deleted successfully.')
                ->with('active_tab', 'orders-and-disposition');
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting order and disposition ID: ' . $id . ' - ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            // Return JSON response for AJAX requests
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete order and disposition.'
                ], 500);
            }

            // Fallback redirect for non-AJAX requests
            return redirect()->route('case.index')
                ->with('error', 'Failed to delete order and disposition: ' . $e->getMessage())
                ->with('active_tab', 'orders-and-disposition');
        }
    }

    /**
     * Get orders and dispositions for a specific case
     */
    public function getByCase($caseId)
    {
        try {
            $case = CaseFile::findOrFail($caseId);
            $ordersAndDispositions = OrderAndDisposition::where('case_id', $caseId)->get();

            ActivityLogger::logAction(
                'VIEW',
                'OrderAndDisposition',
                $case->inspection_id ?? $caseId,
                'Retrieved orders and dispositions for specific case',
                [
                    'establishment' => $case->establishment_name ?? 'Unknown',
                    'records_count' => $ordersAndDispositions->count()
                ]
            );

            return response()->json([
                'case' => $case,
                'orders_and_dispositions' => $ordersAndDispositions
            ]);
        } catch (\Exception $e) {
            Log::error('Error retrieving orders and dispositions for case ID: ' . $caseId . ' - ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve orders and dispositions'
            ], 500);
        }
    }

    /**
     * Get pending finalizations
     */
    public function pendingFinalizations()
    {
        try {
            $pending = OrderAndDisposition::whereNull('date_of_order_actual')
                ->orWhere('status_finalization', '!=', 'completed')
                ->with('case')
                ->get();

            ActivityLogger::logAction(
                'VIEW',
                'OrderAndDisposition',
                null,
                'Retrieved pending finalizations',
                [
                    'pending_count' => $pending->count()
                ]
            );

            return response()->json([
                'data' => $pending
            ]);
        } catch (\Exception $e) {
            Log::error('Error retrieving pending finalizations: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve pending finalizations'
            ], 500);
        }
    }

    /**
     * Bulk update status
     */
    public function bulkUpdateStatus(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:order_and_disposition,id',
            'status_finalization' => 'required|string|max:255'
        ]);

        DB::beginTransaction();
        try {
            OrderAndDisposition::whereIn('id', $validated['ids'])
                ->update(['status_finalization' => $validated['status_finalization']]);

            ActivityLogger::logAction(
                'UPDATE',
                'OrderAndDisposition',
                null,
                'Bulk updated status for multiple records',
                [
                    'records_count' => count($validated['ids']),
                    'new_status' => $validated['status_finalization'],
                    'record_ids' => implode(', ', $validated['ids'])
                ]
            );

            DB::commit();

            return response()->json([
                'message' => 'Status updated successfully for selected records'
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Bulk update status failed: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to bulk update status'
            ], 500);
        }
    }

    /**
     * Handle inline updates via AJAX
     */
    public function inlineUpdate(Request $request, $id)
    {
        Log::info('Orders and disposition inline update request received', [
            'order_id' => $id,
            'request_data' => $request->all(),
            'content_type' => $request->header('Content-Type'),
            'method' => $request->method(),
            'url' => $request->url()
        ]);
        
        DB::beginTransaction();
        try {
            // Lock the order and disposition row
            $orderAndDisposition = OrderAndDisposition::lockForUpdate()->with('case')->findOrFail($id);
            $originalData = $orderAndDisposition->toArray();
            
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
                    if (in_array($key, ['aging_2_days_finalization', 'pct_96_days', 'aging_pct'])) {
                        $cleanedData[$key] = is_numeric($value) ? (int)$value : null;
                    } else {
                        $cleanedData[$key] = is_string($value) ? trim($value) : $value;
                    }
                }
            }
            
            Log::info('Cleaned data for validation', ['cleaned_data' => $cleanedData]);
            
            // Validation rules matching your migration
            $validator = Validator::make($cleanedData, [
                'aging_2_days_finalization' => 'nullable|integer',
                'status_finalization' => 'nullable|string|max:255',
                'pct_96_days' => 'nullable|integer',
                'date_signed_mis' => 'nullable|date',
                'status_pct' => 'nullable|string|max:255',
                'reference_date_pct' => 'nullable|date',
                'aging_pct' => 'nullable|integer',
                'disposition_mis' => 'nullable|string|max:255',
                'disposition_actual' => 'nullable|string|max:255',
                'findings_to_comply' => 'nullable|string',
                'date_of_order_actual' => 'nullable|date',
                'released_date_actual' => 'nullable|date',
            ]);

            if ($validator->fails()) {
                DB::rollBack();
                Log::warning('Orders and disposition validation failed', [
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
            
            Log::info('Orders and disposition update data validated', [
                'validated_data' => $validatedData
            ]);
            
            // Update the order and disposition record
            $orderAndDisposition->update($validatedData);
            Log::info('Orders and disposition updated successfully');

            // Track changes for activity log
            $changeDetails = [];
            foreach ($validatedData as $field => $newValue) {
                $oldValue = $originalData[$field] ?? null;
                if ($oldValue != $newValue) {
                    $fieldLabel = ucfirst(str_replace('_', ' ', $field));
                    $oldDisplay = $oldValue ?? 'empty';
                    $newDisplay = $newValue ?? 'empty';

                    // Format dates for better readability
                    if (in_array($field, ['date_signed_mis', 'reference_date_pct', 'date_of_order_actual', 'released_date_actual'])) {
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
                    'OrderAndDisposition',
                    $orderAndDisposition->case->inspection_id ?? $id,
                    $logDetails,
                    [
                        'establishment' => $orderAndDisposition->case->establishment_name ?? 'Unknown',
                        'fields_count' => count($changeDetails),
                        'method' => 'inline_edit'
                    ]
                );
            } else {
                ActivityLogger::logAction(
                    'UPDATE',
                    'OrderAndDisposition',
                    $orderAndDisposition->case->inspection_id ?? $id,
                    'Attempted update with no changes',
                    [
                        'establishment' => $orderAndDisposition->case->establishment_name ?? 'Unknown',
                        'method' => 'inline_edit'
                    ]
                );
            }

            // Refresh to get updated data
            $orderAndDisposition->refresh();
            
            // Load the case relationship
            $orderAndDisposition->load('case');

            // Prepare response data with proper formatting
            $responseData = [
                'inspection_id' => $orderAndDisposition->case->inspection_id ?? '-',
                'establishment_name' => $orderAndDisposition->case->establishment_name ?? '-',
                'aging_2_days_finalization' => $orderAndDisposition->aging_2_days_finalization ?? '-',
                'status_finalization' => $orderAndDisposition->status_finalization ?? 'Pending',
                'pct_96_days' => $orderAndDisposition->pct_96_days ?? '-',
                'date_signed_mis' => $orderAndDisposition->date_signed_mis ? Carbon::parse($orderAndDisposition->date_signed_mis)->format('Y-m-d') : '-',
                'status_pct' => $orderAndDisposition->status_pct ?? 'Pending',
                'reference_date_pct' => $orderAndDisposition->reference_date_pct ? Carbon::parse($orderAndDisposition->reference_date_pct)->format('Y-m-d') : '-',
                'aging_pct' => $orderAndDisposition->aging_pct ?? '-',
                'disposition_mis' => $orderAndDisposition->disposition_mis ?? '-',
                'disposition_actual' => $orderAndDisposition->disposition_actual ?? '-',
                'findings_to_comply' => $orderAndDisposition->findings_to_comply ?? '-',
                'date_of_order_actual' => $orderAndDisposition->date_of_order_actual ? Carbon::parse($orderAndDisposition->date_of_order_actual)->format('Y-m-d') : '-',
                'released_date_actual' => $orderAndDisposition->released_date_actual ? Carbon::parse($orderAndDisposition->released_date_actual)->format('Y-m-d') : '-',
            ];

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Orders and disposition updated successfully!',
                'data' => $responseData
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            Log::error('Orders and disposition not found: ' . $id);
            return response()->json([
                'success' => false,
                'message' => 'Orders and disposition record not found'
            ], 404);
            
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            Log::error('Database error in orders and disposition update: ' . $e->getMessage(), [
                'order_id' => $id,
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
            Log::error('Orders and disposition inline update failed: ' . $e->getMessage(), [
                'order_id' => $id,
                'request_data' => $request->all(),
                'error_class' => get_class($e),
                'error_line' => $e->getLine(),
                'error_file' => $e->getFile(),
                'stack_trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update orders and disposition: ' . $e->getMessage()
            ], 500);
        }
    }
}