<?php

namespace App\Http\Controllers;

use App\Models\DocumentTracking;
use App\Models\DocumentTrackingHistory;
use App\Models\CaseFile; // ✅ CHANGED from Cases
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DocumentTrackingController extends Controller
{
    public function index()
    {
        $documents = DocumentTracking::with('case')->get();
        $cases = CaseFile::where('overall_status', 'Active')->get(); // ✅ CHANGED
        
        // Count documents by location
        $locationCounts = [
            'Records' => DocumentTracking::where('current_location', 'Records')->count(),
            'MALSU' => DocumentTracking::where('current_location', 'MALSU')->count(),
            'Regional Director' => DocumentTracking::where('current_location', 'Regional Director')->count(),
            'Labor Arbiter' => DocumentTracking::where('current_location', 'Labor Arbiter')->count(),
        ];

        return view('frontend.document-tracking', compact('documents', 'cases', 'locationCounts'));
    }

    public function transfer(Request $request)
    {
        $request->validate([
            'case_id' => 'required|exists:cases,id', // ✅ Table name stays 'cases'
            'current_location' => 'required|string',
            'received_by' => 'required|string',
            'date_received' => 'required|date',
            'notes' => 'nullable|string'
        ]);

        DB::beginTransaction();
        try {
            // Check if document tracking already exists for this case
            $document = DocumentTracking::where('case_id', $request->case_id)->first();

            if ($document) {
                // Save current location to history
                DocumentTrackingHistory::create([
                    'document_tracking_id' => $document->id,
                    'location' => $document->current_location,
                    'received_by' => $document->received_by,
                    'date_received' => $document->date_received,
                    'notes' => $document->notes
                ]);

                // Update current location
                $document->update([
                    'current_location' => $request->current_location === 'Other' ? $request->other_location : $request->current_location,
                    'received_by' => $request->received_by,
                    'date_received' => $request->date_received,
                    'notes' => $request->notes,
                    'status' => 'Active'
                ]);
            } else {
                // Create new document tracking
                $document = DocumentTracking::create([
                    'case_id' => $request->case_id,
                    'current_location' => $request->current_location === 'Other' ? $request->other_location : $request->current_location,
                    'received_by' => $request->received_by,
                    'date_received' => $request->date_received,
                    'notes' => $request->notes,
                    'status' => 'Active'
                ]);
            }

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Document transferred successfully!'
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Failed to transfer document: ' . $e->getMessage()
            ], 500);
        }
    }

    public function history($id)
    {
        $document = DocumentTracking::with(['case', 'history'])->findOrFail($id);
        
        $historyData = [];
        
        // Add current location
        $historyData[] = [
            'location' => $document->current_location,
            'received_by' => $document->received_by,
            'date' => Carbon::parse($document->date_received)->format('M d, Y'),
            'time_ago' => Carbon::parse($document->date_received)->diffForHumans(),
            'notes' => $document->notes
        ];

        // Add historical locations
        foreach ($document->history as $history) {
            $historyData[] = [
                'location' => $history->location,
                'received_by' => $history->received_by,
                'date' => Carbon::parse($history->date_received)->format('M d, Y'),
                'time_ago' => Carbon::parse($history->date_received)->diffForHumans(),
                'notes' => $history->notes
            ];
        }

        return response()->json([
            'success' => true,
            'case_no' => $document->case->case_no ?? 'N/A',
            'establishment' => $document->case->establishment_name ?? 'N/A',
            'history' => $historyData
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'document_id' => 'required|exists:document_tracking,id',
            'current_location' => 'required|string',
            'received_by' => 'required|string',
            'date_received' => 'required|date',
            'notes' => 'nullable|string'
        ]);

        DB::beginTransaction();
        try {
            $document = DocumentTracking::findOrFail($request->document_id);

            // Save current location to history
            DocumentTrackingHistory::create([
                'document_tracking_id' => $document->id,
                'location' => $document->current_location,
                'received_by' => $document->received_by,
                'date_received' => $document->date_received,
                'notes' => $document->notes
            ]);

            // Update document
            $document->update([
                'current_location' => $request->current_location === 'Other' ? $request->other_location : $request->current_location,
                'received_by' => $request->received_by,
                'date_received' => $request->date_received,
                'notes' => $request->notes
            ]);

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Document location updated successfully!'
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update document: ' . $e->getMessage()
            ], 500);
        }
    }
}