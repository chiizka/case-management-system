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
        $roleCounts = [
            'admin' => DocumentTracking::active()->where('current_role', 'admin')->count(),
            'malsu' => DocumentTracking::active()->where('current_role', 'malsu')->count(),
            'case_management' => DocumentTracking::active()->where('current_role', 'case_management')->count(),
            'records' => DocumentTracking::active()->where('current_role', 'records')->count(),
            'provinces' => DocumentTracking::active()->whereIn('current_role', User::PROVINCE_ROLES)->count(),  // Counts all specific provinces
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
            'target_role' => ['required', 'in:' . implode(',', [
                User::ROLE_ADMIN,
                User::ROLE_MALSU,
                User::ROLE_CASE_MANAGEMENT,
                User::ROLE_RECORDS,
                User::ROLE_PROVINCE_ALBAY,
                User::ROLE_PROVINCE_CAMARINES_SUR,
                User::ROLE_PROVINCE_CAMARINES_NORTE,
                User::ROLE_PROVINCE_CATANDUANES,
                User::ROLE_PROVINCE_MASBATE,
                User::ROLE_PROVINCE_SORSOGON,
            ])],
            'transfer_notes' => 'nullable|string'
        ]);

        DB::beginTransaction();
        try {
            $user = Auth::user();
            
            // Check if document tracking exists
            $document = DocumentTracking::where('case_id', $request->case_id)->first();

            if ($document) {
                // ONLY save to history if the document was previously received
                // This preserves the complete chain of custody
                if ($document->status === 'Received' && $document->received_by_user_id) {
                    DocumentTrackingHistory::create([
                        'document_tracking_id' => $document->id,
                        'from_role' => $document->current_role,
                        'to_role' => $document->current_role, // Same role (completed cycle)
                        'transferred_by_user_id' => $document->transferred_by_user_id,
                        'transferred_at' => $document->transferred_at,
                        'received_by_user_id' => $document->received_by_user_id,
                        'received_at' => $document->received_at,
                        'notes' => $document->transfer_notes
                    ]);
                }

                // Update document with NEW transfer (no receiver yet)
                $document->update([
                    'current_role' => $request->target_role,
                    'status' => 'Pending Receipt',
                    'transferred_by_user_id' => $user->id,
                    'transferred_at' => now(),
                    'received_by_user_id' => null, // Clear receiver
                    'received_at' => null, // Clear received time
                    'transfer_notes' => $request->transfer_notes
                ]);
            } else {
                // Create new document tracking (first transfer)
                $document = DocumentTracking::create([
                    'case_id' => $request->case_id,
                    'current_role' => $request->target_role,
                    'status' => 'Pending Receipt',
                    'transferred_by_user_id' => $user->id,
                    'transferred_at' => now(),
                    'transfer_notes' => $request->transfer_notes
                ]);
            }

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Document transferred successfully to ' . DocumentTracking::ROLE_NAMES[$request->target_role] . '!'
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Failed to transfer document: ' . $e->getMessage()
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
        
        // Add current state
        $historyData[] = [
            'role' => DocumentTracking::ROLE_NAMES[$document->current_role],
            'status' => $document->status,
            'transferred_by' => $document->transferredBy ? $document->transferredBy->fname . ' ' . $document->transferredBy->lname : 'System',
            'transferred_at' => $document->transferred_at ? $document->transferred_at->format('M d, Y h:i A') : 'N/A',
            'received_by' => $document->receivedBy ? $document->receivedBy->fname . ' ' . $document->receivedBy->lname : 'Pending',
            'received_at' => $document->received_at ? $document->received_at->format('M d, Y h:i A') : 'Pending',
            'notes' => $document->transfer_notes,
            'time_ago' => $document->transferred_at ? $document->transferred_at->diffForHumans() : 'N/A'
        ];

        // Add historical records
        foreach ($document->history as $history) {
            $historyData[] = [
                'role' => DocumentTracking::ROLE_NAMES[$history->to_role],
                'from_role' => $history->from_role ? DocumentTracking::ROLE_NAMES[$history->from_role] : 'Initial',
                'transferred_by' => $history->transferredBy ? $history->transferredBy->fname . ' ' . $history->transferredBy->lname : 'System',
                'transferred_at' => $history->transferred_at ? $history->transferred_at->format('M d, Y h:i A') : 'N/A',
                'received_by' => $history->receivedBy ? $history->receivedBy->fname . ' ' . $history->receivedBy->lname : 'Not Received',
                'received_at' => $history->received_at ? $history->received_at->format('M d, Y h:i A') : 'N/A',
                'notes' => $history->notes,
                'time_ago' => $history->transferred_at ? $history->transferred_at->diffForHumans() : 'N/A'
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