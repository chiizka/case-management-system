<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CaseFile;

class ArchivedController extends Controller
{
    public function index()
    {
        $archivedCases = CaseFile::where('overall_status', 'Completed')
            ->orderBy('updated_at', 'desc')
            ->get();
        
        return view('frontend.archive', compact('archivedCases'));
    }
}