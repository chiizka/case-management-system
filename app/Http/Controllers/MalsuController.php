<?php

namespace App\Http\Controllers;

use App\Models\Malsu;
use App\Models\CaseFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
}