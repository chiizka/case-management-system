<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CaseFile;

class ArchivedController extends Controller
{
    public function index()
    {
        // Include Completed, Disposed, and Appealed cases in archive
        $archivedCases = CaseFile::whereIn('overall_status', ['Completed', 'Disposed', 'Appealed'])
            ->orderBy('updated_at', 'desc')
            ->get();
        
        return view('frontend.archive', compact('archivedCases'));
    }
}