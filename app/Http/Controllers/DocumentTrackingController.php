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
use App\Models\Malsu;

class DocumentTrackingController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // Get documents based on user role - ONLY ACTIVE CASES
        $myDocuments = DocumentTracking::with(['case', 'transferredBy', 'receivedBy'])
            ->active()
            ->where('current_role', $user->role)
            ->where('status', 'Received')
            ->get();
        
        // Get pending documents for user's role - ONLY ACTIVE CASES
        $pendingDocuments = DocumentTracking::with(['case', 'transferredBy'])
            ->active()
            ->where('current_role', $user->role)
            ->where('status', 'Pending Receipt')
            ->get();
        
        // All documents (for admin overview) - ONLY ACTIVE CASES
        $allDocuments = DocumentTracking::with(['case', 'transferredBy', 'receivedBy'])
            ->active()
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
            $user     = Auth::user();
            $caseId   = $request->case_id;
            $targetRole = $request->target_role;

            $document = DocumentTracking::firstOrCreate(
                ['case_id' => $caseId],
                [
                    'current_role'          => $targetRole,
                    'status'                => 'Pending Receipt',
                    'transferred_by_user_id'=> $user->id,
                    'transferred_at'        => now(),
                    'transfer_notes'        => $request->transfer_notes,
                ]
            );

            if (!$document->wasRecentlyCreated) {
                DocumentTrackingHistory::create([
                    'document_tracking_id'   => $document->id,
                    'from_role'              => $document->current_role,
                    'to_role'                => $document->current_role,
                    'transferred_by_user_id' => $document->transferred_by_user_id,
                    'transferred_at'         => $document->transferred_at,
                    'received_by_user_id'    => $document->received_by_user_id,
                    'received_at'            => $document->received_at,
                    'notes'                  => $document->transfer_notes,
                ]);

                $document->update([
                    'current_role'           => $targetRole,
                    'status'                 => 'Pending Receipt',
                    'transferred_by_user_id' => $user->id,
                    'transferred_at'         => now(),
                    'transfer_notes'         => $request->transfer_notes,
                    'received_by_user_id'    => null,
                    'received_at'            => null,
                ]);
            }

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
        $document = DocumentTracking::findOrFail($id);

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
            if ($latestHistory && !$latestHistory->received_by_user_id) {
                $latestHistory->update([
                    'received_by_user_id' => $user->id,
                    'received_at'         => now()
                ]);
            }

            // ── Auto-create malsu row when MALSU receives a document ──
            if ($document->current_role === User::ROLE_MALSU) {
                \App\Models\Malsu::firstOrCreate(
                    ['case_id' => $document->case_id]
                );
            }

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

    public function history($id)
    {
        $document = DocumentTracking::with([
            'case',
            'history.transferredBy',
            'history.receivedBy',
            'transferredBy',
            'receivedBy'
        ])->findOrFail($id);
        
        $historyData = [];
        
        $historyData[] = [
            'role'           => DocumentTracking::ROLE_NAMES[$document->current_role],
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

        foreach ($document->history as $history) {
            $historyData[] = [
                'role'           => DocumentTracking::ROLE_NAMES[$history->to_role],
                'status'         => 'Completed',
                'transferred_by' => $history->transferredBy
                    ? $history->transferredBy->fname . ' ' . $history->transferredBy->lname
                    : 'System',
                'transferred_at' => $history->transferred_at
                    ? $history->transferred_at->format('M d, Y h:i A')
                    : 'N/A',
                'received_by'    => $history->receivedBy
                    ? $history->receivedBy->fname . ' ' . $history->receivedBy->lname
                    : 'Not Received',
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
            'case_no'       => $document->case->case_no ?? 'N/A',
            'establishment' => $document->case->establishment_name ?? 'N/A',
            'history'       => $historyData
        ]);
    }
}