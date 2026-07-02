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
    public function inlineUpdate(Request $request, $caseId)
    {
        DB::beginTransaction();
        try {
            $case = CaseFile::findOrFail($caseId);
            $malsu = Malsu::firstOrCreate(['case_id' => $caseId]);

            $updateData = $request->except(['_token', '_method', 'id', 'case_tag']);

            // Update malsu fields
            if (!empty($updateData)) {
                $malsu->update($updateData);
            }

            // Handle case_tag separately — it belongs to document_tracking
            if ($request->has('case_tag')) {
                $tracking = $case->documentTracking;
                if ($tracking) {
                    $tracking->update(['case_tag' => $request->input('case_tag') ?: null]);
                }
            }

            ActivityLogger::logAction(
                'UPDATE',
                'Malsu',
                $case->inspection_id,
                "MALSU inline updated",
                ['establishment' => $case->establishment_name]
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'MALSU record updated successfully!',
                'data'    => $malsu,
                'case_tag' => $case->fresh()->documentTracking?->case_tag
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
    
    public function sendToSheriff(Request $request, $caseId)
    {
        $request->validate([
            'sheriff_name' => 'required|string|max:255',
            'target_role'  => ['required', 'in:' . implode(',', User::SHERIFF_ROLES)],
        ]);

        $user = Auth::user();
        if (!$user->isMalsu() && !$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied.'
            ], 403);
        }

        DB::beginTransaction();
        try {
            $case  = CaseFile::findOrFail($caseId);
            $malsu = Malsu::firstOrCreate(['case_id' => $caseId]);

            // Guard: only allow this while the case is actually sitting with MALSU
            $tracking = $case->documentTracking;
            if (!$tracking || $tracking->current_role !== User::ROLE_MALSU) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'This case is not currently with MALSU.'
                ], 422);
            }

            $malsu->update(['sheriff_designate' => $request->sheriff_name]);

            $roleLabel = DocumentTracking::ROLE_NAMES[$request->target_role] ?? $request->target_role;

            app(DocumentTransferService::class)->transferTo(
                $case->id,
                $request->target_role,
                $user->id,
                "Forwarded to sheriff designate {$request->sheriff_name} ({$roleLabel}) by {$user->fname} {$user->lname}"
            );

            ActivityLogger::logAction(
                'TRANSFER',
                'Case',
                $case->inspection_id,
                "Sent to sheriff designate: {$request->sheriff_name} ({$roleLabel})",
                [
                    'establishment' => $case->establishment_name,
                    'sheriff'       => $request->sheriff_name,
                    'target_role'   => $request->target_role,
                ]
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Case sent to {$request->sheriff_name} successfully!",
                'data'    => $malsu->fresh()
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Send to sheriff failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to send: ' . $e->getMessage()
            ], 500);
        }
    }

}