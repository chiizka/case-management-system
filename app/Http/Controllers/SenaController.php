<?php

namespace App\Http\Controllers;

use App\Models\Sena;
use Illuminate\Http\Request;

class SenaController extends Controller
{
    /**
     * Return the rendered SENA tab partial (called via AJAX from case.blade.php)
     */
    public function loadTab()
    {
        $senaRecords = Sena::orderBy('created_at', 'desc')->get();

        $html = view('frontend.partials.sena_tab', [
            'senaRecords' => $senaRecords,
        ])->render();

        return response()->json([
            'success' => true,
            'html'    => $html,
            'count'   => $senaRecords->count(),
        ]);
    }

    /**
     * Inline update for a single field on a SENA record
     */
    public function inlineUpdate(Request $request, $id)
    {
        $sena = Sena::findOrFail($id);

        // Whitelist of fields that can be inline-edited — mirrors the sena table columns
        $allowedFields = [
            'establishment_name',
            'regional_docket_number',
            'sheriff_designate',
            'date_compliance_order',
            'voluntary_compliance',
            'action_taken',
            'full_or_partial',
            'total_gls_monetary_award',
            'total_workers_benefited',
            'amount_penalty_double_indemnity',
            'total_gls_monetary_satisfied',
            'total_workers_satisfied',
            'total_workers_absorbed',
            'complied_oshs_violations',
            'total_penalty_double_indemnity_collected',
            'total_oshs_penalty_admin_fines_collected',
            'date_writ_of_execution_served',
            'date_indorsed_to_po',
            'po_date_received',
            'ro_received_sheriffs_return',
        ];

        $data = $request->only($allowedFields);

        // Only update fields that were actually sent
        $data = array_filter($data, fn($key) => $request->has($key), ARRAY_FILTER_USE_KEY);

        $sena->update($data);

        return response()->json([
            'success' => true,
            'data'    => $sena->fresh(),
        ]);
    }

    /**
     * Create a new SENA record (from the Add SENA Case modal)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'establishment_name'      => 'required|string|max:255',
            'regional_docket_number'  => 'nullable|string|max:100',
            'sheriff_designate'       => 'nullable|string|max:255',
            'date_compliance_order'   => 'nullable|date',
        ]);

        $sena = Sena::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'SENA case added successfully.',
            'data'    => $sena,
        ]);
    }

    /**
     * Delete a SENA record
     */
    public function destroy($id)
    {
        $sena = Sena::findOrFail($id);
        $sena->delete();

        return response()->json([
            'success' => true,
            'message' => 'SENA record deleted successfully.',
        ]);
    }
}