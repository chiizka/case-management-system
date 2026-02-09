<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CaseFile;
use App\Models\CaseAppeal;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ArchivedController extends Controller
{
    /**
     * Display archived cases based on user role
     */
    public function index()
    {
        $user = Auth::user();
        
        if ($user->isProvince()) {
            $provinceName = $user->getProvinceName();
            
            Log::info("Provincial user viewing archived cases", [
                'user' => $user->fname . ' ' . $user->lname,
                'role' => $user->role,
                'province' => $provinceName
            ]);
            
            $query = CaseFile::with('appeal') // ← Add this relationship
                ->where('overall_status', 'Disposed')
                ->where('po_office', $provinceName);
            
        } else {
            Log::info("Non-provincial user viewing all archived cases", [
                'user' => $user->fname . ' ' . $user->lname,
                'role' => $user->role
            ]);
            
            $query = CaseFile::with('appeal') // ← Add this relationship
                ->whereIn('overall_status', ['Completed', 'Disposed', 'Appealed']);
        }
        
        $archivedCases = $query->orderBy('updated_at', 'desc')->get();
        
        Log::info("Archived cases loaded", [
            'count' => $archivedCases->count(),
            'user_role' => $user->role
        ]);
        
        return view('frontend.archive', compact('archivedCases'));
    }
    
    /**
     * Store appeal information for an archived case
     */
    public function storeAppeal(Request $request, $caseId)
    {
        $request->validate([
            'appellate_body' => 'required|in:Office of the Secretary,NLRC,BLR',
            'transmittal_date' => 'required|date',
            'notes' => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();

            // Find the case
            $case = CaseFile::findOrFail($caseId);

            // Check if already appealed
            if ($case->appeal) {
                return response()->json([
                    'success' => false,
                    'message' => 'This case has already been appealed.'
                ], 400);
            }

            // Create appeal record
            $appeal = CaseAppeal::create([
                'case_id' => $caseId,
                'appellate_body' => $request->appellate_body,
                'transmittal_date' => $request->transmittal_date,
                'destination' => 'Central Office - Manila',
                'notes' => $request->notes
            ]);

            // Update case overall_status to "Appealed"
            $case->overall_status = 'Appealed';
            $case->save();

            DB::commit();

            Log::info("Case appealed successfully", [
                'case_id' => $caseId,
                'case_no' => $case->case_no,
                'appellate_body' => $request->appellate_body,
                'user' => Auth::user()->fname . ' ' . Auth::user()->lname
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Case successfully marked as appealed and sent to ' . $request->appellate_body,
                'appeal' => $appeal
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error("Failed to appeal case", [
                'case_id' => $caseId,
                'error' => $e->getMessage(),
                'user' => Auth::user()->fname . ' ' . Auth::user()->lname
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to process appeal: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Export archived cases
     */
    public function export()
    {
        $user = Auth::user();
        
        if ($user->isProvince()) {
            $provinceName = $user->getProvinceName();
            $query = CaseFile::where('overall_status', 'Disposed')
                ->where('po_office', $provinceName);
        } else {
            $query = CaseFile::whereIn('overall_status', ['Completed', 'Disposed', 'Appealed']);
        }
        
        $archivedCases = $query->orderBy('updated_at', 'desc')->get();
        
        return response()->json([
            'success' => true,
            'cases' => $archivedCases
        ]);
    }
}