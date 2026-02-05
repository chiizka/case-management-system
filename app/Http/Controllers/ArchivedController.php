<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CaseFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ArchivedController extends Controller
{
    /**
     * Display archived cases based on user role
     * 
     * Filtering Logic:
     * - Provincial users: See only DISPOSED cases from their province (po_office matches)
     * - Non-provincial users (Admin, MALSU, Case Management, Records): See all archived cases
     */
    public function index()
    {
        $user = Auth::user();
        
        if ($user->isProvince()) {
            // Provincial users: ONLY show Disposed cases from their province
            $provinceName = $user->getProvinceName();
            
            Log::info("Provincial user viewing archived cases", [
                'user' => $user->fname . ' ' . $user->lname,
                'role' => $user->role,
                'province' => $provinceName
            ]);
            
            $query = CaseFile::where('overall_status', 'Disposed')
                ->where('po_office', $provinceName);
            
        } else {
            // Non-provincial users: See ALL archived cases (Completed, Disposed, Appealed)
            Log::info("Non-provincial user viewing all archived cases", [
                'user' => $user->fname . ' ' . $user->lname,
                'role' => $user->role
            ]);
            
            $query = CaseFile::whereIn('overall_status', ['Completed', 'Disposed', 'Appealed']);
        }
        
        $archivedCases = $query->orderBy('updated_at', 'desc')->get();
        
        Log::info("Archived cases loaded", [
            'count' => $archivedCases->count(),
            'user_role' => $user->role
        ]);
        
        return view('frontend.archive', compact('archivedCases'));
    }
    
    /**
     * Export archived cases (with same filtering logic)
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