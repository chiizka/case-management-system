<?php

namespace App\Http\Controllers;

use App\Models\Malsu;
use App\Models\CaseFile;
use App\Models\User;
use App\Models\DocumentTracking;
use App\Services\DocumentTransferService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Helpers\ActivityLogger;

class MalsuController extends Controller
{
    public function inlineUpdate(Request $request, $malsuId)
    {
        DB::beginTransaction();
        try {
            $malsu = Malsu::findOrFail($malsuId);

            $updateData = $request->except(['_token', '_method', 'id', 'case_tag']);

            // Clearing sheriff_designate should also clear the linked user
            if (array_key_exists('sheriff_designate', $updateData) && $updateData['sheriff_designate'] === '') {
                $updateData['assigned_sheriff_user_id'] = null;
            }

            if (!empty($updateData)) {
                $malsu->update($updateData);
            }

            // case_tag lives on document_tracking — only reachable when a real case exists
            if ($request->has('case_tag') && $malsu->case_id) {
                $tracking = $malsu->case?->documentTracking;
                if ($tracking) {
                    $tracking->update(['case_tag' => $request->input('case_tag') ?: null]);
                }
            }

            ActivityLogger::logAction(
                'UPDATE',
                'Malsu',
                $malsu->case?->inspection_id ?? ('Legacy MALSU #' . $malsu->id),
                "MALSU inline updated",
                ['establishment' => $malsu->case?->establishment_name ?? $malsu->case_title]
            );

            DB::commit();

            return response()->json([
                'success'  => true,
                'message'  => 'MALSU record updated successfully!',
                'data'     => $malsu,
                'case_tag' => $malsu->case?->fresh()?->documentTracking?->case_tag
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('MALSU inline update failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to update: ' . $e->getMessage()
            ], 500);
        }
    }

    public function sendToSheriff(Request $request, $malsuId)
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
            $malsu = Malsu::findOrFail($malsuId);
            $roleLabel = DocumentTracking::ROLE_NAMES[$targetRole] ?? $targetRole;

            if (!$malsu->case_id) {
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
                    'inspection_id'      => 'LEGACY-' . $malsu->id,
                    'establishment_name' => $malsu->case_title ?: ('Legacy MALSU Record #' . $malsu->id),
                    'po_office'          => $provinceName,
                    'current_stage'      => '7: Appeals & Resolution',
                    'overall_status'     => 'Active',
                ]);

                $case->computeFields();
                $case->saveQuietly();

                $malsu->case_id = $case->id;

                DocumentTracking::create([
                    'case_id'                => $case->id,
                    'current_role'           => $targetRole,
                    'status'                 => 'Pending Receipt',
                    'transferred_by_user_id' => $user->id,
                    'transferred_at'         => now(),
                    'transfer_notes'         => "Legacy MALSU record converted to case and forwarded to sheriff designate {$sheriffName} ({$roleLabel}) by {$user->fname} {$user->lname}",
                ]);

                ActivityLogger::logAction(
                    'CREATE',
                    'Case',
                    $case->inspection_id,
                    "Auto-created from legacy MALSU record #{$malsu->id} and sent to sheriff designate: {$sheriffName} ({$roleLabel})",
                    ['establishment' => $case->establishment_name]
                );

            } else {
                $case = $malsu->case;
                $tracking = $case->documentTracking;
                if (!$tracking || $tracking->current_role !== User::ROLE_MALSU) {
                    DB::rollBack();
                    return response()->json(['success' => false, 'message' => 'This case is not currently with MALSU.'], 422);
                }

                app(DocumentTransferService::class)->transferTo(
                    $case->id,
                    $targetRole,
                    $user->id,
                    "Forwarded to sheriff designate {$sheriffName} ({$roleLabel}) by {$user->fname} {$user->lname}"
                );
            }

            $malsu->sheriff_designate         = $sheriffName;
            $malsu->assigned_sheriff_user_id  = $sheriff->id;
            $malsu->save();

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
                'data'    => $malsu->fresh()
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Send to sheriff failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to send: ' . $e->getMessage()], 500);
        }
    }
}