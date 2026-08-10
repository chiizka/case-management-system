<?php

namespace App\Http\Controllers;

use App\Models\Sena;
use Illuminate\Http\Request;
use App\Models\CaseFile;
use App\Models\DocumentTracking;
use App\Models\Malsu;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Helpers\ActivityLogger;

class SenaController extends Controller
{
    /**
     * Return the rendered SENA tab partial (called via AJAX from case.blade.php)
     */
    public function loadTab()
    {
        $senaRecords = Sena::where(function ($q) {
                $q->whereNull('case_id')
                ->orWhere(function ($q2) {
                    $q2->whereHas('case.documentTracking', function ($q3) {
                        $q3->where('status', '!=', 'Received');
                    })
                    ->orWhereDoesntHave('case.documentTracking');
                });
            })
            ->orderBy('created_at', 'desc')
            ->get();

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
        DB::beginTransaction();
        try {
            $sena = Sena::findOrFail($id);

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
            $data = array_filter($data, fn($key) => $request->has($key), ARRAY_FILTER_USE_KEY);

            if (!empty($data)) {
                $sena->update($data);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'data'    => $sena->fresh(),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('SENA inline update failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to update: ' . $e->getMessage()
            ], 500);
        }
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

    public function sendToSheriff(Request $request, $senaId)
    {
        $request->validate([
            'sheriff_user_id' => 'required|exists:users,id',
        ]);

        $user = Auth::user();
        if (!$user->isMalsu() && !$user->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'Access denied.'], 403);
        }

        $sheriff = User::findOrFail($request->sheriff_user_id);

        if (!in_array($sheriff->role, User::SHERIFF_ROLES)) {
            return response()->json(['success' => false, 'message' => 'Selected user is not a sheriff.'], 422);
        }

        $targetRole  = $sheriff->role;
        $sheriffName = trim($sheriff->fname . ' ' . $sheriff->lname);

        DB::beginTransaction();
        try {
            $sena = Sena::findOrFail($senaId);
            $roleLabel = DocumentTracking::ROLE_NAMES[$targetRole] ?? $targetRole;

            if ($sena->case_id) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'This SENA record is already linked to a case.'
                ], 422);
            }

            $provinceKey  = str_replace('sheriff_', '', $targetRole);
            $provinceName = User::PROVINCES[$provinceKey] ?? null;

            if (!$provinceName) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Could not determine province for the selected sheriff.'
                ], 422);
            }

            $case = CaseFile::create([
                'inspection_id'      => 'SENA-' . $sena->id,
                'case_no'            => $sena->regional_docket_number,
                'establishment_name' => $sena->establishment_name ?: ('SENA Record #' . $sena->id),
                'po_office'          => $provinceName,
                'current_stage'      => '7: Appeals & Resolution',
                'overall_status'     => 'Active',
            ]);

            $case->computeFields();
            $case->saveQuietly();

            $sena->case_id = $sena->case_id ?? $case->id;

            DocumentTracking::create([
                'case_id'                => $case->id,
                'current_role'           => $targetRole,
                'status'                 => 'Pending Receipt',
                'transferred_by_user_id' => $user->id,
                'transferred_at'         => now(),
                'transfer_notes'         => "SENA record converted to case and forwarded to sheriff designate {$sheriffName} ({$roleLabel}) by {$user->fname} {$user->lname}",
                'case_tag'               => 'SENA',
            ]);

            // Link into the Malsu table so this case shows up in the same
            // combined sheriff/province views as regular MALSU cases.
            Malsu::create([
                'case_id'                => $case->id,
                'case_title'             => $sena->establishment_name,
                'regional_docket_number' => $sena->regional_docket_number,
                'date_compliance_order'  => $sena->date_compliance_order,
                'sheriff_designate'      => $sheriffName,
                'assigned_sheriff_user_id' => $sheriff->id,
            ]);

            ActivityLogger::logAction(
                'CREATE',
                'Case',
                $case->inspection_id,
                "Auto-created from SENA record #{$sena->id} and sent to sheriff designate: {$sheriffName} ({$roleLabel})",
                ['establishment' => $case->establishment_name]
            );

            $sena->sheriff_designate = $sheriffName;
            $sena->save();

            ActivityLogger::logAction(
                'TRANSFER',
                'Case',
                $case->inspection_id,
                "Sent to sheriff designate: {$sheriffName} ({$roleLabel})",
                ['establishment' => $case->establishment_name, 'sheriff' => $sheriffName, 'target_role' => $targetRole]
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Case sent to {$sheriffName} successfully!",
                'data'    => $sena->fresh()
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('SENA send to sheriff failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to send: ' . $e->getMessage()], 500);
        }
    }
}