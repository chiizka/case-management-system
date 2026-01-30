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
        
        // Get documents based on user role - ONLY ACTIVE CASES
        $myDocuments = DocumentTracking::with(['case', 'transferredBy', 'receivedBy'])
            ->active() // Use the scope
            ->where('current_role', $user->role)
            ->where('status', 'Received')
            ->get();
        
        // Get pending documents for user's role - ONLY ACTIVE CASES
        $pendingDocuments = DocumentTracking::with(['case', 'transferredBy'])
            ->active() // Use the scope
            ->where('current_role', $user->role)
            ->where('status', 'Pending Receipt')
            ->get();
        
        // All documents (for admin overview) - ONLY ACTIVE CASES
        $allDocuments = DocumentTracking::with(['case', 'transferredBy', 'receivedBy'])
            ->active() // Use the scope
            ->get();
        
        $cases = CaseFile::where('overall_status', 'Active')->get();
        
        // Count documents by role - ONLY ACTIVE CASES
        // Fixed: Now includes individual province counts
        $roleCounts = [
            'admin' => DocumentTracking::active()->where('current_role', 'admin')->count(),
            'malsu' => DocumentTracking::active()->where('current_role', 'malsu')->count(),
            'case_management' => DocumentTracking::active()->where('current_role', 'case_management')->count(),
            'records' => DocumentTracking::active()->where('current_role', 'records')->count(),
            
            // Individual province counts
            'province_albay' => DocumentTracking::active()->where('current_role', 'province_albay')->count(),
            'province_camarines_sur' => DocumentTracking::active()->where('current_role', 'province_camarines_sur')->count(),
            'province_camarines_norte' => DocumentTracking::active()->where('current_role', 'province_camarines_norte')->count(),
            'province_catanduanes' => DocumentTracking::active()->where('current_role', 'province_catanduanes')->count(),
            'province_masbate' => DocumentTracking::active()->where('current_role', 'province_masbate')->count(),
            'province_sorsogon' => DocumentTracking::active()->where('current_role', 'province_sorsogon')->count(),
        ];

        return view('frontend.document-tracking', compact(
            'myDocuments',
            'pendingDocuments',
            'allDocuments',
            'cases',
            'roleCounts'
        ));
    }

    public function transfer(Request $request)
    {
        $request->validate([
            'case_id' => 'required|exists:cases,id',
            'target_role' => ['required', 'in:' . implode(',', User::VALID_ROLES)],
            'transfer_notes' => 'nullable|string'
        ]);

        DB::beginTransaction();
        try {
            $user = Auth::user();
            $caseId = $request->case_id;
            $targetRole = $request->target_role;

            // Find or create tracking record
            $document = DocumentTracking::firstOrCreate(
                ['case_id' => $caseId],
                [
                    'current_role' => $targetRole,
                    'status' => 'Pending Receipt',
                    'transferred_by_user_id' => $user->id,
                    'transferred_at' => now(),
                    'transfer_notes' => $request->transfer_notes,
                ]
            );

            // If it already existed → this is a real transfer
            if (!$document->wasRecentlyCreated) {
                // Save the COMPLETE OLD CYCLE to history
                DocumentTrackingHistory::create([
                    'document_tracking_id' => $document->id,
                    'from_role' => $document->current_role,
                    'to_role' => $document->current_role,  // Keep it in the same role for the cycle
                    'transferred_by_user_id' => $document->transferred_by_user_id,  // OLD transfer
                    'transferred_at' => $document->transferred_at,  // OLD transfer time
                    'received_by_user_id' => $document->received_by_user_id,
                    'received_at' => $document->received_at,
                    'notes' => $document->transfer_notes,  // OLD notes
                ]);

                // Now update current tracking with NEW transfer
                $document->update([
                    'current_role' => $targetRole,
                    'status' => 'Pending Receipt',
                    'transferred_by_user_id' => $user->id,
                    'transferred_at' => now(),
                    'transfer_notes' => $request->transfer_notes,
                    'received_by_user_id' => null,
                    'received_at' => null,
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
        $user = Auth::user();
        $document = DocumentTracking::findOrFail($id);

        // Check if user's role matches the document's target role
        if ($document->current_role !== $user->role) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to receive this document.'
            ], 403);
        }

        // Check if already received
        if ($document->status === 'Received') {
            return response()->json([
                'success' => false,
                'message' => 'This document has already been received.'
            ], 400);
        }

        DB::beginTransaction();
        try {
            // Update document as received
            $document->update([
                'status' => 'Received',
                'received_by_user_id' => $user->id,
                'received_at' => now()
            ]);

            // Update the latest history record with receiver info
            $latestHistory = $document->history()->latest()->first();
            if ($latestHistory && !$latestHistory->received_by_user_id) {
                $latestHistory->update([
                    'received_by_user_id' => $user->id,
                    'received_at' => now()
                ]);
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
        $document = DocumentTracking::with(['case', 'history.transferredBy', 'history.receivedBy', 'transferredBy', 'receivedBy'])->findOrFail($id);
        
        $historyData = [];
        
        // Add current state (ongoing cycle)
        $historyData[] = [
            'role' => DocumentTracking::ROLE_NAMES[$document->current_role],
            'status' => $document->status,
            'transferred_by' => $document->transferredBy ? $document->transferredBy->fname . ' ' . $document->transferredBy->lname : 'System',
            'transferred_at' => $document->transferred_at ? $document->transferred_at->format('M d, Y h:i A') : 'N/A',
            'received_by' => $document->receivedBy ? $document->receivedBy->fname . ' ' . $document->receivedBy->lname : 'Awaiting Receipt',
            'received_at' => $document->received_at ? $document->received_at->format('M d, Y h:i A') : 'Not Yet Received',
            'notes' => $document->transfer_notes,
            'time_ago' => $document->transferred_at ? $document->transferred_at->diffForHumans() : 'N/A',
            'is_current' => true  // Flag to identify current state
        ];

        // Add completed historical cycles
        foreach ($document->history as $history) {
            $historyData[] = [
                'role' => DocumentTracking::ROLE_NAMES[$history->to_role],
                'status' => 'Completed',
                'transferred_by' => $history->transferredBy ? $history->transferredBy->fname . ' ' . $history->transferredBy->lname : 'System',
                'transferred_at' => $history->transferred_at ? $history->transferred_at->format('M d, Y h:i A') : 'N/A',
                'received_by' => $history->receivedBy ? $history->receivedBy->fname . ' ' . $history->receivedBy->lname : 'Not Received',
                'received_at' => $history->received_at ? $history->received_at->format('M d, Y h:i A') : 'N/A',
                'notes' => $history->notes,
                'time_ago' => $history->transferred_at ? $history->transferred_at->diffForHumans() : 'N/A',
                'is_current' => false
            ];
        }

        return response()->json([
            'success' => true,
            'case_no' => $document->case->case_no ?? 'N/A',
            'establishment' => $document->case->establishment_name ?? 'N/A',
            'history' => $historyData
        ]);
    }
    
}