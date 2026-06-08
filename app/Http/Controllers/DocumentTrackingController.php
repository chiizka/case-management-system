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

class DocumentTrackingController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Documents currently at this user's role and already received
        $myDocuments = DocumentTracking::with(['case', 'transferredBy', 'receivedBy'])
            ->active()
            ->where('current_role', $user->role)
            ->where('status', 'Received')
            ->get();

        // Documents currently at this user's role and awaiting receipt
        $pendingDocuments = DocumentTracking::with(['case', 'transferredBy'])
            ->active()
            ->where('current_role', $user->role)
            ->where('status', 'Pending Receipt')
            ->get();

        // All documents (admin overview)
        $allDocuments = DocumentTracking::with(['case', 'transferredBy', 'receivedBy'])
            ->active()
            ->get();

        $cases = CaseFile::where('overall_status', 'Active')->get();

        // Per-role document counts
        $roleCounts = [
            'admin'                    => DocumentTracking::active()->where('current_role', 'admin')->count(),
            'malsu'                    => DocumentTracking::active()->where('current_role', 'malsu')->count(),
            'case_management'          => DocumentTracking::active()->where('current_role', 'case_management')->count(),
            'records'                  => DocumentTracking::active()->where('current_role', 'records')->count(),
            'province_albay'           => DocumentTracking::active()->where('current_role', 'province_albay')->count(),
            'province_camarines_sur'   => DocumentTracking::active()->where('current_role', 'province_camarines_sur')->count(),
            'province_camarines_norte' => DocumentTracking::active()->where('current_role', 'province_camarines_norte')->count(),
            'province_catanduanes'     => DocumentTracking::active()->where('current_role', 'province_catanduanes')->count(),
            'province_masbate'         => DocumentTracking::active()->where('current_role', 'province_masbate')->count(),
            'province_sorsogon'        => DocumentTracking::active()->where('current_role', 'province_sorsogon')->count(),
        ];

        // ── Forwarded to MALSU ────────────────────────────────────────────────
        // Show cases that have EVER been transferred from case_management → malsu.
        // Stays until the case is archived (active() scope handles this).
        $forwardedToMalsu = DocumentTracking::with([
                'case',
                'transferredBy',
                'receivedBy',
                'history.transferredBy',
                'history.receivedBy',
            ])
            ->active()
            ->whereHas('history', fn ($h) =>
                $h->where('from_role', 'case_management')
                  ->where('to_role', 'malsu')
            )
            ->get();

        // ── Forwarded to Case Management ──────────────────────────────────────
        // Show cases that have EVER been transferred from malsu → case_management.
        // Stays until the case is archived.
        $forwardedToCaseManagement = DocumentTracking::with([
                'case',
                'transferredBy',
                'receivedBy',
                'history.transferredBy',
                'history.receivedBy',
            ])
            ->active()
            ->whereHas('history', fn ($h) =>
                $h->where('from_role', 'malsu')
                  ->where('to_role', 'case_management')
            )
            ->get();

        return view('frontend.document-tracking', compact(
            'myDocuments',
            'pendingDocuments',
            'allDocuments',
            'cases',
            'roleCounts',
            'forwardedToMalsu',
            'forwardedToCaseManagement'
        ));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Transfer
    // ─────────────────────────────────────────────────────────────────────────
    public function transfer(Request $request)
    {
        $request->validate([
            'case_id'        => 'required|exists:cases,id',
            'target_role'    => ['required', 'in:' . implode(',', User::VALID_ROLES)],
            'transfer_notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $user       = Auth::user();
            $caseId     = $request->case_id;
            $targetRole = $request->target_role;

            $document = DocumentTracking::firstOrCreate(
                ['case_id' => $caseId],
                [
                    'current_role'           => $targetRole,
                    'status'                 => 'Pending Receipt',
                    'transferred_by_user_id' => $user->id,
                    'transferred_at'         => now(),
                    'transfer_notes'         => $request->transfer_notes,
                ]
            );

            if (!$document->wasRecentlyCreated) {
                // Save the completed cycle to history with CORRECT direction:
                // from_role = where it WAS, to_role = where it is GOING
                DocumentTrackingHistory::create([
                    'document_tracking_id'   => $document->id,
                    'from_role'              => $document->current_role, // where it was
                    'to_role'                => $targetRole,             // where it's going
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
            return response()->json(['success' => true, 'message' => 'Document transferred successfully!']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Transfer failed: ' . $e->getMessage()], 500);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Receive
    // ─────────────────────────────────────────────────────────────────────────
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
                'received_at'         => now(),
            ]);

            // Stamp the latest history entry's received info if it belongs to this cycle
            $latestHistory = $document->history()->latest()->first();
            if ($latestHistory && !$latestHistory->received_by_user_id) {
                $latestHistory->update([
                    'received_by_user_id' => $user->id,
                    'received_at'         => now(),
                ]);
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Document received successfully!']);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['success' => false, 'message' => 'Failed to receive document: ' . $e->getMessage()], 500);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // History — newest first, correct from→to direction
    // ─────────────────────────────────────────────────────────────────────────
    public function history($id)
    {
        $document = DocumentTracking::with([
            'case',
            'history.transferredBy',
            'history.receivedBy',
            'transferredBy',
            'receivedBy',
        ])->findOrFail($id);

        $historyData = [];

        // ── 1. Current active cycle — always shown first (most recent) ────────
        $currentRole      = $document->current_role;
        $isReturnedNote   = $document->transfer_notes
                                && str_contains($document->transfer_notes, '[RETURNED]');
        $transferredByRole = optional($document->transferredBy)->role;

        $historyData[] = [
            'from_role'      => $transferredByRole,
            'to_role'        => $currentRole,
            'from_role_name' => $transferredByRole
                                    ? (DocumentTracking::ROLE_NAMES[$transferredByRole]
                                        ?? ucfirst(str_replace('_', ' ', $transferredByRole)))
                                    : null,
            'role'           => DocumentTracking::ROLE_NAMES[$currentRole]
                                    ?? ucfirst(str_replace('_', ' ', $currentRole)),
            'status'         => $isReturnedNote ? 'Returned (Pending Receipt)' : $document->status,
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
            'is_current'     => true,
        ];

        // ── 2. Past completed cycles — newest first ───────────────────────────
        foreach ($document->history->sortByDesc('transferred_at') as $h) {
            $isReturned = $h->notes && str_contains($h->notes, '[RETURNED]');

            $historyData[] = [
                'from_role'      => $h->from_role,
                'to_role'        => $h->to_role,
                'from_role_name' => $h->from_role
                                        ? (DocumentTracking::ROLE_NAMES[$h->from_role]
                                            ?? ucfirst(str_replace('_', ' ', $h->from_role)))
                                        : null,
                'role'           => $h->to_role
                                        ? (DocumentTracking::ROLE_NAMES[$h->to_role]
                                            ?? ucfirst(str_replace('_', ' ', $h->to_role)))
                                        : 'Unknown',
                'status'         => $isReturned ? 'Returned' : 'Completed',
                'transferred_by' => $h->transferredBy
                                        ? $h->transferredBy->fname . ' ' . $h->transferredBy->lname
                                        : 'System',
                'transferred_at' => $h->transferred_at
                                        ? $h->transferred_at->format('M d, Y h:i A')
                                        : 'N/A',
                'received_by'    => $h->receivedBy
                                        ? $h->receivedBy->fname . ' ' . $h->receivedBy->lname
                                        : 'Not Received',
                'received_at'    => $h->received_at
                                        ? $h->received_at->format('M d, Y h:i A')
                                        : 'N/A',
                'notes'          => $h->notes,
                'time_ago'       => $h->transferred_at
                                        ? $h->transferred_at->diffForHumans()
                                        : 'N/A',
                'is_current'     => false,
            ];
        }

        return response()->json([
            'success'       => true,
            'case_no'       => $document->case->case_no ?? 'N/A',
            'establishment' => $document->case->establishment_name ?? 'N/A',
            'history'       => $historyData,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Return document (case_management ↔ malsu only)
    // ─────────────────────────────────────────────────────────────────────────
    public function returnDocument(Request $request, $id)
    {
        $request->validate([
            'transfer_notes' => 'nullable|string|max:1000',
        ]);

        DB::beginTransaction();
        try {
            $user     = Auth::user();
            $document = DocumentTracking::with('transferredBy')->findOrFail($id);

            $allowedPairs = [
                'malsu'           => 'case_management',
                'case_management' => 'malsu',
            ];

            if ($document->current_role !== $user->role) {
                return response()->json([
                    'success' => false,
                    'message' => 'You can only return documents currently assigned to your role.'
                ], 403);
            }

            if (!isset($allowedPairs[$user->role])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Return is only allowed between Case Management and MALSU.'
                ], 403);
            }

            $senderRole     = optional($document->transferredBy)->role;
            $expectedSender = $allowedPairs[$user->role];

            if ($senderRole !== $expectedSender) {
                return response()->json([
                    'success' => false,
                    'message' => 'This document was not sent by '
                                . (DocumentTracking::ROLE_NAMES[$expectedSender] ?? $expectedSender)
                                . ', so it cannot be returned.',
                ], 403);
            }

            if ($document->status !== 'Received') {
                return response()->json([
                    'success' => false,
                    'message' => 'You must receive the document before returning it.'
                ], 400);
            }

            $targetRole = $expectedSender;
            $returnNote = trim('[RETURNED] ' . ($request->transfer_notes ?? ''));

            // Save current cycle to history with correct direction
            DocumentTrackingHistory::create([
                'document_tracking_id'   => $document->id,
                'from_role'              => $document->current_role,
                'to_role'                => $targetRole,
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
                'transfer_notes'         => $returnNote,
                'received_by_user_id'    => null,
                'received_at'            => null,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Document returned to '
                            . (DocumentTracking::ROLE_NAMES[$targetRole] ?? $targetRole)
                            . ' successfully!',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Return failed: ' . $e->getMessage()
            ], 500);
        }
    }
}