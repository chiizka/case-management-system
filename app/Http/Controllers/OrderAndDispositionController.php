<?php

namespace App\Http\Controllers;

use App\Models\OrderAndDisposition;
use App\Models\CaseFile;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

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
}