<?php

namespace App\Http\Controllers;

use App\Models\OrderAndDisposition;
use App\Models\CaseFile;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class OrderAndDispositionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
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

        $orderAndDisposition = OrderAndDisposition::create($validated);

        return response()->json([
            'message' => 'Order and Disposition created successfully',
            'data' => $orderAndDisposition->load('case')
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(OrderAndDisposition $orderAndDisposition)
    {
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

        $orderAndDisposition->update($validated);

        return response()->json([
            'message' => 'Order and Disposition updated successfully',
            'data' => $orderAndDisposition->load('case')
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(OrderAndDisposition $orderAndDisposition)
    {
        $orderAndDisposition->delete();

        return response()->json([
            'message' => 'Order and Disposition deleted successfully'
        ]);
    }

    /**
     * Get orders and dispositions for a specific case
     */
    public function getByCase($caseId)
    {
        $case = CaseFile::findOrFail($caseId);
        $ordersAndDispositions = OrderAndDisposition::where('case_id', $caseId)->get();

        return response()->json([
            'case' => $case,
            'orders_and_dispositions' => $ordersAndDispositions
        ]);
    }

    /**
     * Get pending finalizations
     */
    public function pendingFinalizations()
    {
        $pending = OrderAndDisposition::whereNull('date_of_order_actual')
            ->orWhere('status_finalization', '!=', 'completed')
            ->with('case')
            ->get();

        return response()->json([
            'data' => $pending
        ]);
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

        OrderAndDisposition::whereIn('id', $validated['ids'])
            ->update(['status_finalization' => $validated['status_finalization']]);

        return response()->json([
            'message' => 'Status updated successfully for selected records'
        ]);
    }

    
        public function inlineUpdate(Request $request, $id)
        {
            Log::info('Orders and disposition inline update request received', [
                'order_id' => $id,
                'request_data' => $request->all(),
                'content_type' => $request->header('Content-Type'),
                'method' => $request->method(),
                'url' => $request->url()
            ]);
            
            try {
                $orderAndDisposition = OrderAndDisposition::findOrFail($id);
                
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
                $validator = \Illuminate\Support\Facades\Validator::make($cleanedData, [
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
                    'date_signed_mis' => $orderAndDisposition->date_signed_mis ? \Carbon\Carbon::parse($orderAndDisposition->date_signed_mis)->format('Y-m-d') : '-',
                    'status_pct' => $orderAndDisposition->status_pct ?? 'Pending',
                    'reference_date_pct' => $orderAndDisposition->reference_date_pct ? \Carbon\Carbon::parse($orderAndDisposition->reference_date_pct)->format('Y-m-d') : '-',
                    'aging_pct' => $orderAndDisposition->aging_pct ?? '-',
                    'disposition_mis' => $orderAndDisposition->disposition_mis ?? '-',
                    'disposition_actual' => $orderAndDisposition->disposition_actual ?? '-',
                    'findings_to_comply' => $orderAndDisposition->findings_to_comply ?? '-',
                    'date_of_order_actual' => $orderAndDisposition->date_of_order_actual ? \Carbon\Carbon::parse($orderAndDisposition->date_of_order_actual)->format('Y-m-d') : '-',
                    'released_date_actual' => $orderAndDisposition->released_date_actual ? \Carbon\Carbon::parse($orderAndDisposition->released_date_actual)->format('Y-m-d') : '-',
                ];

                return response()->json([
                    'success' => true,
                    'message' => 'Orders and disposition updated successfully!',
                    'data' => $responseData
                ]);

            } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                Log::error('Orders and disposition not found: ' . $id);
                return response()->json([
                    'success' => false,
                    'message' => 'Orders and disposition record not found'
                ], 404);
                
            } catch (\Illuminate\Database\QueryException $e) {
                Log::error('Database error in orders and disposition update: ' . $e->getMessage(), [
                    'order_id' => $id,
                    'sql_state' => $e->errorInfo[0] ?? 'Unknown',
                    'error_code' => $e->errorInfo[1] ?? 'Unknown',
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Database error occurred. Please check your data and try again.'
                ], 500);
                
            } catch (\Exception $e) {
                Log::error('Orders and disposition inline update failed: ' . $e->getMessage(), [
                    'order_id' => $id,
                    'request_data' => $request->all(),
                    'error_class' => get_class($e),
                    'error_line' => $e->getLine(),
                    'error_file' => $e->getFile(),
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update orders and disposition: ' . $e->getMessage()
                ], 500);
            }
        }
}