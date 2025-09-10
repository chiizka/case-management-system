<?php

namespace App\Http\Controllers;

use App\Models\docketing;
use App\Models\CaseFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class DocketingController extends Controller
{
    /**
     * Display a listing of docketings in the combined case view.
     */
    public function index()
    {
        $cases = CaseFile::all();
        $docketing = docketing::with('case')->get(); // Changed to singular to match view

        // Optional: Debugging data
        // dd([
        //     'cases_count' => $cases->count(),
        //     'docketing_count' => $docketing->count(),
        //     'docketing_data' => $docketing->toArray()
        // ]);

        return view('frontend.case', compact('cases', 'docketing'));
    }

    /**
     * Show the form for creating a new docketing (combined view).
     */
    public function create()
    {
        $cases = CaseFile::all();
        $docketing = docketing::with('case')->get(); // Changed to singular
        return view('frontend.case', compact('cases', 'docketing'));
    }

    /**
     * Store a newly created docketing in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'case_id' => 'required|exists:cases,id',
            'pct_for_docketing' => 'nullable|numeric',
            'date_scheduled_docketed' => 'nullable|date',
            'aging_docket' => 'nullable|numeric',
            'status_docket' => 'nullable|string|max:255',
            'hearing_officer_mis' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        docketing::create($request->all());

        return redirect()->route('docketing.index')
            ->with('success', 'Docketing created successfully.')
            ->with('active_tab', 'docketing');
    }

    /**
     * Display the specified docketing in the combined view.
     */
    public function show($id)
    {
        $docketingRecord = docketing::with('case')->findOrFail($id);

        // Access case data through the relationship - FIXED
        $inspectionId = $docketingRecord->case->inspection_id ?? null;
        $establishment = $docketingRecord->case->establishment_name ?? null;

        $cases = CaseFile::all();
        $docketing = docketing::with('case')->get(); // Changed to singular
        return view('frontend.case', compact('cases', 'docketing', 'docketingRecord'));
    }

    /**
     * Show the form for editing the specified docketing in combined view.
     */
    public function edit($id)
    {
        $docketingRecord = docketing::with('case')->findOrFail($id);

        // Access case data through the relationship - FIXED
        $inspectionId = $docketingRecord->case->inspection_id ?? null;
        $establishment = $docketingRecord->case->establishment_name ?? null;

        $cases = CaseFile::all();
        $docketing = docketing::with('case')->get(); // Changed to singular
        return view('frontend.case', compact('cases', 'docketing', 'docketingRecord'));
    }

    /**
     * Update the specified docketing in storage.
     */
    public function update(Request $request, $id)
    {
        $docketingRecord = docketing::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'case_id' => 'required|exists:cases,id',
            'pct_for_docketing' => 'nullable|numeric',
            'date_scheduled_docketed' => 'nullable|date',
            'aging_docket' => 'nullable|numeric',
            'status_docket' => 'nullable|string|max:255',
            'hearing_officer_mis' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $docketingRecord->update($request->all());

        return redirect()->route('docketing.index')
            ->with('success', 'Docketing updated successfully.')
            ->with('active_tab', 'docketing');
    }

    /**
     * Remove the specified docketing from storage.
     */
    public function destroy($id)
    {
        try {
            $docketingRecord = docketing::findOrFail($id);
            $docketingRecord->delete();
            Log::info('Docketing ID: ' . $id . ' deleted successfully.');

            return redirect()->route('docketing.index')
                ->with('success', 'Docketing deleted successfully.')
                ->with('active_tab', 'docketing');
        } catch (\Exception $e) {
            Log::error('Error deleting docketing ID: ' . $id . ' - ' . $e->getMessage());
            return redirect()->route('docketing.index')
                ->with('error', 'Failed to delete docketing: ' . $e->getMessage())
                ->with('active_tab', 'docketing');
        }
    }
}