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