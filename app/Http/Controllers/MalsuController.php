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

            // Get or create the malsu record for this case
            $malsu = Malsu::firstOrCreate(['case_id' => $caseId]);

            $updateData = $request->except(['_token', '_method', 'id']);

            $malsu->update($updateData);

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
                'data'    => $malsu
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