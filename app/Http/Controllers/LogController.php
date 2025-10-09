<?php

namespace App\Http\Controllers;

use App\Models\Log;
use Illuminate\Http\Request;

class LogController extends Controller
{
    public function index()
    {
        $logs = \App\Models\Log::with('user')->latest()->get();
        return view('logs.index', compact('logs'));
    }
}
