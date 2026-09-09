<?php

namespace App\Services;

use App\Models\DocumentTracking;
use App\Models\DocumentTrackingHistory;

class DocumentTransferService
{
    /**
     * Transfer a case's document to a new role.
     * Mirrors the exact logic used in DocumentTrackingController::transfer(),
     * so any place that needs to move a case between roles (province → malsu,
     * malsu → sheriff, etc.) goes through this single implementation.
     */
    public function transferTo(int $caseId, string $targetRole, int $userId, ?string $notes = null): DocumentTracking
    {
        $document = DocumentTracking::firstOrCreate(
            ['case_id' => $caseId],
            [
                'current_role'           => $targetRole,
                'status'                 => 'Pending Receipt',
                'transferred_by_user_id' => $userId,
                'transferred_at'         => now(),
                'transfer_notes'         => $notes,
            ]
        );

        if (!$document->wasRecentlyCreated) {
            // If the document's current state is just the revert marker left
            // by DocumentTrackingController::revertPendingTransfer() (cancel
            // or decline), that cancellation is already fully logged in its
            // own DocumentTrackingHistory row. Archiving this marker state
            // again here would just re-describe the same cancel/decline as
            // a second, redundant history card. Skip the archive write in
            // that one case — everything else below still runs normally.
            $isJustReverted = is_null($document->transferred_by_user_id)
                && $document->transfer_notes
                && str_starts_with($document->transfer_notes, 'Reverted to');

            if (!$isJustReverted) {
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
            }

            $document->update([
                // Snapshot pre-transfer state so a pending transfer can be
                // cancelled (by sender) or declined (by receiver) and reverted
                // without depending on from_role/to_role in history.
                'previous_role'                => $document->current_role,
                'previous_status'               => $document->status,
                'previous_received_by_user_id'  => $document->received_by_user_id,
                'previous_received_at'          => $document->received_at,
                'previous_transfer_notes'        => $document->transfer_notes,
                'previous_case_tag'              => $document->case_tag,

                'current_role'           => $targetRole,
                'status'                 => 'Pending Receipt',
                'transferred_by_user_id' => $userId,
                'transferred_at'         => now(),
                'transfer_notes'         => $notes,
                'received_by_user_id'    => null,
                'received_at'            => null,
            ]);
        }

        return $document->fresh();
    }
}