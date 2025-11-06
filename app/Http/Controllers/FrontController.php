<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CaseFile;
use App\Models\Inspection;
use App\Models\DocumentTracking;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class FrontController extends Controller
{
public function index()
{
    // Statistics Cards
    $totalCases = CaseFile::count();
    $activeCases = CaseFile::where('overall_status', 'active')->count();
    
    // Pending Inspections
    $pendingInspections = Inspection::where(function($query) {
        $query->whereNull('date_of_nr')
              ->orWhere('lapse_20_day_period', '>=', Carbon::now()->format('Y-m-d'));
    })->count();
    
    $closedCases = CaseFile::where('overall_status', 'completed')->count();
    
    $userRole = Auth::user()->role;
    $pendingDocuments = DocumentTracking::where('current_role', $userRole)
        ->where('status', 'Pending Receipt')
        ->whereNull('received_by_user_id')
        ->with(['case', 'transferredBy'])
        ->orderBy('transferred_at', 'desc')
        ->limit(5) // Show only top 5 on dashboard
        ->get();
    
    // Count total pending for this user's role
    $totalPendingDocs = DocumentTracking::where('current_role', $userRole)
        ->where('status', 'Pending Receipt')
        ->whereNull('received_by_user_id')
        ->count();
    
    // Cases by Stage Distribution
    $stageData = [
        CaseFile::where('current_stage', 'inspection')->count(),
        CaseFile::where('current_stage', 'docketing')->count(),
        CaseFile::where('current_stage', 'hearing')->count(),
        CaseFile::where('current_stage', 'resolution')->count(),
    ];
        
        // Monthly Trend (Last 6 Months)
        $monthLabels = [];
        $monthlyData = [];
        
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $monthLabels[] = $date->format('M Y');
            
            $count = CaseFile::whereYear('created_at', $date->year)
                         ->whereMonth('created_at', $date->month)
                         ->count();
            $monthlyData[] = $count;
        }
        
        // Cases by Location (Top 5) - Using establishment_name as location
        $locationStats = CaseFile::select('establishment_name', DB::raw('count(*) as total'))
                             ->whereNotNull('establishment_name')
                             ->where('establishment_name', '!=', '')
                             ->groupBy('establishment_name')
                             ->orderByDesc('total')
                             ->limit(5)
                             ->get();
        
        $locationLabels = $locationStats->pluck('establishment_name')->toArray();
        $locationData = $locationStats->pluck('total')->toArray();
        
        // If no location data, provide defaults
        if (empty($locationLabels)) {
            $locationLabels = ['No Data Available'];
            $locationData = [0];
        }
        
        // Priority Breakdown - Based on current_stage as priority indicator
        // You may need to add a 'priority' column or adjust this logic
        $criticalCases = CaseFile::where('current_stage', 'hearing')->count(); // Assuming hearing is critical
        $highCases = CaseFile::where('current_stage', 'docketing')->count();
        $mediumCases = CaseFile::where('current_stage', 'inspection')->count();
        $lowCases = CaseFile::where('current_stage', 'resolution')->count();
        
        $totalForPercentage = $totalCases > 0 ? $totalCases : 1;
        
        $criticalPercentage = round(($criticalCases / $totalForPercentage) * 100);
        $highPercentage = round(($highCases / $totalForPercentage) * 100);
        $mediumPercentage = round(($mediumCases / $totalForPercentage) * 100);
        $lowPercentage = round(($lowCases / $totalForPercentage) * 100);
        
        // Recent Cases (Last 10)
        $recentCases = CaseFile::with('inspections')
                           ->orderBy('created_at', 'desc')
                           ->limit(10)
                           ->get();
        
        // Document Tracking Statistics
        $documentsInTransit = DocumentTracking::where('status', 'in_transit')->count();
        $documentsPending = DocumentTracking::where('status', 'pending')->count();
        $documentsReceived = DocumentTracking::where('status', 'received')->count();
        
        $totalDocuments = $documentsInTransit + $documentsPending + $documentsReceived;
        $totalDocuments = $totalDocuments > 0 ? $totalDocuments : 1;
        
        $documentsInTransitPercent = round(($documentsInTransit / $totalDocuments) * 100);
        $documentsPendingPercent = round(($documentsPending / $totalDocuments) * 100);
        $documentsReceivedPercent = round(($documentsReceived / $totalDocuments) * 100);
        
        return view('frontend.index', compact(
            'totalCases',
            'activeCases',
            'pendingInspections',
            'closedCases',
            'pendingDocuments',        // ADD THIS
            'totalPendingDocs',         // ADD THIS
            'stageData',
            'monthLabels',
            'monthlyData',
            'locationLabels',
            'locationData',
            'criticalCases',
            'highCases',
            'mediumCases',
            'lowCases',
            'criticalPercentage',
            'highPercentage',
            'mediumPercentage',
            'lowPercentage',
            'recentCases',
            'documentsInTransit',
            'documentsPending',
            'documentsReceived',
            'documentsInTransitPercent',
            'documentsPendingPercent',
            'documentsReceivedPercent'
        ));
    }

    public function login()
    {
        return view('frontend.login');
    }

    public function users()
    {
        $users = \App\Models\User::all();
        return view('frontend.users', compact('users'));
    }
}