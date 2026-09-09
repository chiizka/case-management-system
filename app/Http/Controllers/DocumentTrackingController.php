<?php

namespace App\Http\Controllers;

use App\Models\DocumentTracking;
use App\Models\DocumentTrackingHistory;
use App\Models\CaseFile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Services\DocumentTransferService;
use App\Models\Malsu;
use App\Helpers\ActivityLogger;

class DocumentTrackingController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        $myDocumentsQuery = DocumentTracking::with(['case.malsu', 'transferredBy', 'receivedBy'])
            ->active()
            ->where('current_role', $user->role)
            ->where('status', 'Received')
            ->orderByDesc('received_at');

        $pendingDocumentsQuery = DocumentTracking::with(['case.malsu', 'transferredBy'])
            ->active()
            ->where('current_role', $user->role)
            ->where('status', 'Pending Receipt')
            ->orderByDesc('transferred_at');

        // Sheriffs only see documents assigned specifically to them,
        // not everyone sharing the same province sheriff role.
        if ($user->isSheriff()) {
            $myDocumentsQuery->whereHas('case.malsu', function ($q) use ($user) {
                $q->where('assigned_sheriff_user_id', $user->id);
            });
            $pendingDocumentsQuery->whereHas('case.malsu', function ($q) use ($user) {
                $q->where('assigned_sheriff_user_id', $user->id);
            });
        }

        $myDocuments = $myDocumentsQuery->get();
        $pendingDocuments = $pendingDocumentsQuery->get();

        // Transfers this user's role sent that are still awaiting receipt —
        // powers the "Sent — Awaiting Receipt" tab and its Cancel button.
        // Admin sees every outstanding sent transfer, regardless of role.
        $sentPendingDocumentsQuery = DocumentTracking::with(['case.malsu'])
            ->active()
            ->where('status', 'Pending Receipt')
            ->where(function ($q) use ($user) {
                $q->where('previous_role', $user->role);
                if ($user->isAdmin()) {
                    $q->orWhereNotNull('previous_role');
                }
            });

        $sentPendingDocuments = $sentPendingDocumentsQuery->get();
        
        // All documents (for admin overview) - ONLY ACTIVE CASES
        $allDocuments = DocumentTracking::with(['case.malsu', 'transferredBy', 'receivedBy'])
            ->active()
            ->orderByDesc('updated_at')
            ->get();
        
        $cases = CaseFile::where('overall_status', 'Active')->get();

        // ─────────────────────────────────────────────────────────────────
        // Cases Forwarded to MALSU (visible to admin + case_management only)
        // A case enters this list the moment it has EVER been transferred
        // to malsu (current tracking OR any history record).
        // It stays here until the case is archived/completed/disposed.
        // ─────────────────────────────────────────────────────────────────
        $casesForwardedToMalsu = collect();

        if ($user->isAdmin() || $user->isCaseManagement()) {

            // Collect case IDs that have ever touched 'malsu':
            // 1) Currently sitting at malsu (pending or received)
            $currentlyAtMalsu = DocumentTracking::where('current_role', 'malsu')
                ->pluck('case_id');

            // 2) Previously passed through malsu (recorded in history)
            $historicallyAtMalsu = DocumentTrackingHistory::where('to_role', 'malsu')
                ->join('document_tracking', 'document_tracking.id', '=', 'document_tracking_history.document_tracking_id')
                ->pluck('document_tracking.case_id');

            $allMalsuCaseIds = $currentlyAtMalsu->merge($historicallyAtMalsu)->unique()->values();

            // Load those cases with their tracking + full history, excluding archived
            $casesForwardedToMalsu = CaseFile::with([
                'documentTracking.transferredBy',
                'documentTracking.receivedBy',
                'documentTracking.history.transferredBy',
                'documentTracking.history.receivedBy',
            ])
            ->whereIn('id', $allMalsuCaseIds)
            ->where('inspection_id', 'not like', 'LEGACY-%')
            ->where('inspection_id', 'not like', 'MALSU-%')
            ->whereNotIn('overall_status', ['Completed', 'Disposed', 'Appealed'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($case) {
                $tracking = $case->documentTracking;
                if (!$tracking) return null;

                // Date first forwarded to malsu — check history first (oldest malsu entry),
                // fall back to current tracking if currently at malsu
                $firstMalsuHistory = $tracking->history
                    ->where('to_role', 'malsu')
                    ->sortBy('transferred_at')
                    ->first();

                $dateFirstForwarded = null;
                if ($firstMalsuHistory) {
                    $dateFirstForwarded = $firstMalsuHistory->transferred_at;
                } elseif ($tracking->current_role === 'malsu') {
                    $dateFirstForwarded = $tracking->transferred_at;
                }

                // Latest transfer note — from most recent history or current tracking
                $latestNote = $tracking->transfer_notes;
                if ($tracking->history->isNotEmpty()) {
                    $latestNote = $tracking->history->first()->notes ?? $latestNote;
                }

                // All transfer notes concatenated for the wide notes column
                $allNotes = collect();
                // Add historical notes (newest to oldest — history() is ordered desc)
                foreach ($tracking->history as $h) {
                    if ($h->notes) {
                        $allNotes->push('[' . ($h->transferred_at ? $h->transferred_at->format('M d, Y') : 'N/A') . '] ' . $h->notes);
                    }
                }
                // Add current tracking note
                if ($tracking->transfer_notes) {
                    $allNotes->push('[' . ($tracking->transferred_at ? $tracking->transferred_at->format('M d, Y') : 'N/A') . '] ' . $tracking->transfer_notes);
                }

                $case->_malsu_date_first_forwarded = $dateFirstForwarded;
                $case->_malsu_current_location     = $tracking->current_role;
                $case->_malsu_current_status       = $tracking->status;
                $case->_malsu_all_notes            = $allNotes->reverse()->implode("\n");

                return $case;
            })
            ->filter() // remove any nulls (cases without tracking)
            ->values();
        }

        // ─────────────────────────────────────────────────────────────────
        // Cases Forwarded to Case Management (visible to admin + malsu only)
        // A case enters this list the moment MALSU transfers it to
        // case_management. It stays here permanently until archived —
        // even if case_management sends it back to malsu or elsewhere.
        //
        // HOW transfer() WORKS (key insight):
        //   When any user transfers a doc, the OLD cycle is written to
        //   document_tracking_history with transferred_by_user_id = the
        //   person who originally sent it. So when malsu sends to
        //   case_management, history permanently records transferred_by
        //   = malsu user. Even after case_management returns it, that
        //   history row is never deleted — the case stays visible here.
        // ─────────────────────────────────────────────────────────────────
        $casesForwardedToCaseManagement = collect();

        if ($user->isAdmin() || $user->isMalsu()) {

            $malsuUserIds = User::where('role', 'malsu')->pluck('id');

            // 1) PERMANENT: any history row where a malsu user was the sender.
            //    Once written, this record is never removed — so the case
            //    stays in this tab forever regardless of where it moves next.
            //    Table prefix on transferred_by_user_id avoids ambiguity with
            //    the joined document_tracking table which has the same column.
            $historicallySentByCaseIds = DocumentTrackingHistory::whereIn('document_tracking_history.transferred_by_user_id', $malsuUserIds)
                ->join('document_tracking', 'document_tracking.id', '=', 'document_tracking_history.document_tracking_id')
                ->pluck('document_tracking.case_id');

            // 2) LIVE CYCLE: currently being transferred by a malsu user but
            //    not yet archived to history (pending receipt at case_management).
            $currentlySentByCaseIds = DocumentTracking::whereIn('transferred_by_user_id', $malsuUserIds)
                ->where('current_role', 'case_management')
                ->pluck('case_id');

            $allCmCaseIds = $historicallySentByCaseIds
                ->merge($currentlySentByCaseIds)
                ->unique()
                ->values();

            $casesForwardedToCaseManagement = CaseFile::with([
                'documentTracking.transferredBy',
                'documentTracking.receivedBy',
                'documentTracking.history.transferredBy',
                'documentTracking.history.receivedBy',
            ])
            ->whereIn('id', $allCmCaseIds)
            ->whereNotIn('overall_status', ['Completed', 'Disposed', 'Appealed'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($case) {
                $tracking = $case->documentTracking;
                if (!$tracking) return null;

                // Date first forwarded to case_management BY malsu.
                // Since from_role/to_role in history both store the LEAVING role
                // (not the destination), we identify malsu-sent entries by
                // checking transferred_by_user_id against malsu user IDs instead.
                $malsuUserIds = User::where('role', 'malsu')->pluck('id');

                $firstCmHistory = $tracking->history
                    ->filter(fn($h) => $malsuUserIds->contains($h->transferred_by_user_id))
                    ->sortBy('transferred_at')
                    ->first();

                $dateFirstForwarded = null;
                if ($firstCmHistory) {
                    // Oldest history row sent by a malsu user
                    $dateFirstForwarded = $firstCmHistory->transferred_at;
                } elseif (
                    $tracking->current_role === 'case_management' &&
                    $malsuUserIds->contains($tracking->transferred_by_user_id)
                ) {
                    // Live cycle: currently at case_management, sent by malsu
                    $dateFirstForwarded = $tracking->transferred_at;
                }

                // Build concatenated notes from all transfers in history + current
                $allNotes = collect();
                foreach ($tracking->history as $h) {
                    if ($h->notes) {
                        $allNotes->push(
                            '[' . ($h->transferred_at ? $h->transferred_at->format('M d, Y') : 'N/A') . '] '
                            . $h->notes
                        );
                    }
                }
                if ($tracking->transfer_notes) {
                    $allNotes->push(
                        '[' . ($tracking->transferred_at ? $tracking->transferred_at->format('M d, Y') : 'N/A') . '] '
                        . $tracking->transfer_notes
                    );
                }

                $case->_cm_date_first_forwarded = $dateFirstForwarded;
                $case->_cm_current_location     = $tracking->current_role;
                $case->_cm_current_status       = $tracking->status;
                $case->_cm_all_notes            = $allNotes->reverse()->implode("\n");

                return $case;
            })
            ->filter()
            ->values();
        }

        // Count documents by role - ONLY ACTIVE CASES
        $roleCounts = [
            'admin'                  => DocumentTracking::active()->where('current_role', 'admin')->count(),
            'malsu'                  => DocumentTracking::active()->where('current_role', 'malsu')->count(),
            'case_management'        => DocumentTracking::active()->where('current_role', 'case_management')->count(),
            'records'                => DocumentTracking::active()->where('current_role', 'records')->count(),
            'province_albay'         => DocumentTracking::active()->where('current_role', 'province_albay')->count(),
            'province_camarines_sur' => DocumentTracking::active()->where('current_role', 'province_camarines_sur')->count(),
            'province_camarines_norte'=> DocumentTracking::active()->where('current_role', 'province_camarines_norte')->count(),
            'province_catanduanes'   => DocumentTracking::active()->where('current_role', 'province_catanduanes')->count(),
            'province_masbate'       => DocumentTracking::active()->where('current_role', 'province_masbate')->count(),
            'province_sorsogon'      => DocumentTracking::active()->where('current_role', 'province_sorsogon')->count(),
        ];

        return view('frontend.document-tracking', compact(
            'myDocuments',
            'pendingDocuments',
            'sentPendingDocuments',
            'allDocuments',
            'cases',
            'roleCounts',
            'casesForwardedToMalsu',
            'casesForwardedToCaseManagement'
        ));
    }

    public function transfer(Request $request)
    {
        $request->validate([
            'case_id'        => 'required|exists:cases,id',
            'target_role'    => ['required', 'in:' . implode(',', User::VALID_ROLES)],
            'transfer_notes' => 'nullable|string'
        ]);

        DB::beginTransaction();
        try {
            $user = Auth::user();
            $case = CaseFile::findOrFail($request->case_id);
            $targetRoleLabel = DocumentTracking::ROLE_NAMES[$request->target_role] ?? $request->target_role;

            app(DocumentTransferService::class)->transferTo(
                $request->case_id,
                $request->target_role,
                $user->id,
                $request->transfer_notes
            );

            ActivityLogger::logAction(
                'TRANSFER',
                'Case',
                $case->inspection_id ?? ('Case #' . $case->id),
                "Document transferred to {$targetRoleLabel}" . ($request->transfer_notes ? " — {$request->transfer_notes}" : ''),
                ['establishment' => $case->establishment_name, 'target_role' => $request->target_role]
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Document transferred successfully!'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Transfer failed: ' . $e->getMessage()
            ], 500);
        }
    }

    public function receive(Request $request, $id)
    {
        $user     = Auth::user();
        $document = DocumentTracking::with('case')->findOrFail($id);

        if ($document->current_role !== $user->role) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to receive this document.'
            ], 403);
        }

        if ($document->status === 'Received') {
            return response()->json([
                'success' => false,
                'message' => 'This document has already been received.'
            ], 400);
        }

        DB::beginTransaction();
        try {
            $document->update([
                'status'              => 'Received',
                'received_by_user_id' => $user->id,
                'received_at'         => now()
            ]);

            $latestHistory = $document->history()->latest()->first();
            if ($latestHistory && !$latestHistory->received_by_user_id && $latestHistory->transferred_by_user_id) {
                $latestHistory->update([
                    'received_by_user_id' => $user->id,
                    'received_at'         => now()
                ]);
            }

            // ── Auto-create malsu row when MALSU receives a document ──
            if ($document->current_role === User::ROLE_MALSU) {
                \App\Models\Malsu::firstOrCreate(
                    ['case_id' => $document->case_id],
                    ['regional_docket_number' => $document->case?->case_no]
                );
            }

            $roleLabel = DocumentTracking::ROLE_NAMES[$document->current_role] ?? $document->current_role;

            ActivityLogger::logAction(
                'RECEIVE',
                'Case',
                $document->case?->inspection_id ?? ('Case #' . $document->case_id),
                "Document received at {$roleLabel}",
                ['establishment' => $document->case?->establishment_name]
            );

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Document received successfully!'
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Failed to receive document: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancel a pending transfer — undoes it back to where it was before
     * this transfer. Only the role that sent it (previous_role) or Admin
     * may do this, and only while it hasn't been received yet.
     */
    public function cancel($id)
    {
        $user     = Auth::user();
        $document = DocumentTracking::with('case')->findOrFail($id);

        if ($document->status !== 'Pending Receipt') {
            return response()->json([
                'success' => false,
                'message' => 'This transfer is no longer pending — it may have already been received.'
            ], 400);
        }

        if ($document->previous_role !== $user->role && !$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to cancel this transfer.'
            ], 403);
        }

        return $this->revertPendingTransfer($document, $user, 'cancelled');
    }

    /**
     * Decline a transfer that's pending receipt at your role — sends it
     * back to where it came from. Only the receiving role (current_role)
     * or Admin may do this, and only while it hasn't been received yet.
     */
    public function decline($id)
    {
        $user     = Auth::user();
        $document = DocumentTracking::with('case')->findOrFail($id);

        if ($document->status !== 'Pending Receipt') {
            return response()->json([
                'success' => false,
                'message' => 'This transfer is no longer pending — it may have already been received.'
            ], 400);
        }

        if ($document->current_role !== $user->role && !$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to decline this transfer.'
            ], 403);
        }

        return $this->revertPendingTransfer($document, $user, 'declined');
    }

    /**
     * Shared revert logic for cancel/decline — restores the tracking row
     * from its previous_* snapshot, logs a history entry, and clears the
     * snapshot fields.
     */
    private function revertPendingTransfer(DocumentTracking $document, $user, string $actionLabel)
    {
        if (!$document->previous_role) {
            return response()->json([
                'success' => false,
                'message' => 'No prior state recorded for this document — cannot revert automatically.'
            ], 422);
        }

        DB::beginTransaction();
        try {
            $fromRole = $document->current_role;
            $toRole   = $document->previous_role;

            // Capture who actually sent this leg, and when, before the
            // update() below overwrites transferred_by_user_id/transferred_at.
            // "Transferred By" on this card should credit the real sender
            // (e.g. Albay Province), not the person cancelling/declining it.
            $originalSenderId = $document->transferred_by_user_id;
            $originalSentAt   = $document->transferred_at;

            DocumentTrackingHistory::create([
                'document_tracking_id'   => $document->id,
                'from_role'              => $fromRole,
                'to_role'                => $toRole,
                'transferred_by_user_id' => $originalSenderId,
                'transferred_at'         => $originalSentAt,
                // Not a genuine receipt — but recording the actor here (with
                // the real cancel/decline timestamp) lets history() label it
                // clearly as "Declined by X" / "Cancelled by X" instead of a
                // bare, misleading "Not Received".
                'received_by_user_id'    => $user->id,
                'received_at'            => now(),
                'notes'                  => "Transfer to " . (DocumentTracking::ROLE_NAMES[$fromRole] ?? $fromRole)
                    . " {$actionLabel} by {$user->fname} {$user->lname} — reverted to "
                    . (DocumentTracking::ROLE_NAMES[$toRole] ?? $toRole) . ".",
            ]);

            if ($document->isSheriffRole() && $document->case?->malsu) {
                $document->case->malsu->update(['sheriff_designate' => null]);
            }

            $document->update([
                'current_role'           => $document->previous_role,
                'status'                 => $document->previous_status,
                'received_by_user_id'    => $document->previous_received_by_user_id,
                'received_at'            => $document->previous_received_at,
                'case_tag'               => $document->previous_case_tag,

                // Deliberately NOT restoring transfer_notes from the snapshot:
                // for a case's first-ever transfer, previous_transfer_notes is
                // literally the original "Case created by..." text, and with
                // received_by restored to the creator too, the live row would
                // satisfy the blade isLikelyCreation heuristic again — showing
                // a second "Created" card that duplicates the real one already
                // in document_tracking_history. Describe the revert instead.
                'transfer_notes'         => "Reverted to " . (DocumentTracking::ROLE_NAMES[$toRole] ?? $toRole)
                    . " — transfer {$actionLabel} by {$user->fname} {$user->lname}.",
                'transferred_by_user_id' => null,
                'transferred_at'         => now(),

                'previous_role'                => null,
                'previous_status'               => null,
                'previous_received_by_user_id'  => null,
                'previous_received_at'          => null,
                'previous_transfer_notes'        => null,
                'previous_case_tag'              => null,
            ]);

            ActivityLogger::logAction(
                'TRANSFER',
                'Case',
                $document->case?->inspection_id ?? ('Case #' . $document->case_id),
                "Pending transfer to " . (DocumentTracking::ROLE_NAMES[$fromRole] ?? $fromRole) . " {$actionLabel}",
                ['establishment' => $document->case?->establishment_name]
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Transfer {$actionLabel} successfully."
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to revert transfer: ' . $e->getMessage()
            ], 500);
        }
    }

    public function history($id)
    {
    $document = DocumentTracking::with([
        'case.malsu',
        'history.transferredBy',
        'history.receivedBy',
        'transferredBy',
        'receivedBy'
    ])->findOrFail($id);
        
        $historyData = [];

        // After a cancel/decline, the live tracking row's "current state" is
        // just a restatement of the history entry revertPendingTransfer()
        // already wrote (same event, same timestamp) — showing both is
        // redundant. Detect that state via the markers set there and skip
        // the synthetic card in that case; the real history row below still
        // covers it.
        $isJustReverted = is_null($document->transferred_by_user_id)
            && $document->transfer_notes
            && str_starts_with($document->transfer_notes, 'Reverted to');

        if (!$isJustReverted) {
        $historyData[] = [
            'role'           => DocumentTracking::ROLE_NAMES[$document->current_role],
            'role_key'       => $document->current_role,
            'status'         => $document->status, 
            'transferred_by' => $document->transferredBy
                ? $document->transferredBy->fname . ' ' . $document->transferredBy->lname
                : 'System',
            'transferred_at' => $document->transferred_at
                ? $document->transferred_at->format('M d, Y h:i A')
                : 'N/A',
            'received_by'    => $document->receivedBy
                ? $document->receivedBy->fname . ' ' . $document->receivedBy->lname
                : 'Awaiting Receipt',
            'received_at'    => $document->received_at
                ? $document->received_at->format('M d, Y h:i A')
                : 'Not Yet Received',
            'notes'          => $document->transfer_notes,
            'time_ago'       => $document->transferred_at
                ? $document->transferred_at->diffForHumans()
                : 'N/A',
            'is_current'     => true
        ];
        }

        foreach ($document->history as $history) {
            // revertPendingTransfer() writes a distinctive "— reverted to"
            // marker into its notes — use it to relabel "Received By" as
            // "Declined by X" / "Cancelled by X" rather than implying a
            // genuine receipt happened.
            $isRevertEntry = $history->notes && str_contains($history->notes, '— reverted to');
            $receivedByName = $history->receivedBy
                ? $history->receivedBy->fname . ' ' . $history->receivedBy->lname
                : 'Not Received';

            if ($isRevertEntry && $history->receivedBy) {
                $actionWord = str_contains($history->notes, 'declined by') ? 'Declined' : 'Cancelled';
                $receivedByName = "{$actionWord} by {$receivedByName}";
            }

            $historyData[] = [
                'role'           => DocumentTracking::ROLE_NAMES[$history->from_role] ?? $history->from_role,
                'role_key'       => $history->from_role,
                'to_role'        => $history->to_role
                    ? (DocumentTracking::ROLE_NAMES[$history->to_role] ?? $history->to_role)
                    : null,
                'status'         => 'Completed',
                'transferred_by' => $history->transferredBy
                    ? $history->transferredBy->fname . ' ' . $history->transferredBy->lname
                    : 'System',
                'transferred_at' => $history->transferred_at
                    ? $history->transferred_at->format('M d, Y h:i A')
                    : 'N/A',
                'received_by'    => $receivedByName,
                'received_at'    => $history->received_at
                    ? $history->received_at->format('M d, Y h:i A')
                    : 'N/A',
                'notes'          => $history->notes,
                'time_ago'       => $history->transferred_at
                    ? $history->transferred_at->diffForHumans()
                    : 'N/A',
                'is_current'     => false
            ];
        }

        return response()->json([
            'success'       => true,
            'case_no'       => $document->case->case_no
                ?? optional($document->case->malsu)->regional_docket_number
                ?? $document->case->inspection_id
                ?? 'N/A',
            'establishment' => $document->case->establishment_name ?? 'N/A',
            'history'       => $historyData
        ]);
    }
}