<?php

namespace App\Http\Controllers;

use App\Models\HearingProcess;
use App\Models\CaseFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class HearingProcessController extends Controller
{
    /**
     * Display a listing of hearing processes in the combined case view.
     */
    public function index()
    {
        $cases = CaseFile::all();
        $hearingProcess = HearingProcess::with('case')->get();

        return view('frontend.case', compact('cases', 'hearingProcess'));
    }

    /**
     * Show the form for creating a new hearing process (combined view).
     */
    public function create()
    {
        $cases = CaseFile::all();
        $hearingProcess = HearingProcess::with('case')->get();
        return view('frontend.case', compact('cases', 'hearingProcess'));
    }

    /**
     * Store a newly created hearing process in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'case_id' => 'required|exists:cases,id',
            'date_1st_mc_actual' => 'nullable|date',
            'first_mc_pct' => 'nullable|date',
            'status_1st_mc' => 'nullable|string|max:255',
            'date_2nd_last_mc' => 'nullable|date',
            'second_last_mc_pct' => 'nullable|date',
            'status_2nd_mc' => 'nullable|string|max:255',
            'case_folder_forwarded_to_ro' => 'nullable|string|max:255',
            'complete_case_folder' => 'nullable|in:Y,N',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        HearingProcess::create($request->all());

        return redirect()->route('hearing-process.index')
            ->with('success', 'Hearing process created successfully.')
            ->with('active_tab', 'hearing');
    }

    /**
     * Display the specified hearing process in the combined view.
     */
    public function show($id)
    {
        $hearingProcessRecord = HearingProcess::with('case')->findOrFail($id);

        // Access case data through the relationship
        $inspectionId = $hearingProcessRecord->case->inspection_id ?? null;
        $establishment = $hearingProcessRecord->case->establishment_name ?? null;

        $cases = CaseFile::all();
        $hearingProcess = HearingProcess::with('case')->get();
        return view('frontend.case', compact('cases', 'hearingProcess', 'hearingProcessRecord'));
    }

    /**
     * Show the form for editing the specified hearing process in combined view.
     */
    public function edit($id)
    {
        $hearingProcessRecord = HearingProcess::with('case')->findOrFail($id);

        // Access case data through the relationship
        $inspectionId = $hearingProcessRecord->case->inspection_id ?? null;
        $establishment = $hearingProcessRecord->case->establishment_name ?? null;

        $cases = CaseFile::all();
        $hearingProcess = HearingProcess::with('case')->get();
        return view('frontend.case', compact('cases', 'hearingProcess', 'hearingProcessRecord'));
    }

    /**
     * Update the specified hearing process in storage.
     */
    public function update(Request $request, $id)
    {
        $hearingProcessRecord = HearingProcess::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'case_id' => 'required|exists:cases,id',
            'date_1st_mc_actual' => 'nullable|date',
            'first_mc_pct' => 'nullable|date',
            'status_1st_mc' => 'nullable|string|max:255',
            'date_2nd_last_mc' => 'nullable|date',
            'second_last_mc_pct' => 'nullable|date',
            'status_2nd_mc' => 'nullable|string|max:255',
            'case_folder_forwarded_to_ro' => 'nullable|string|max:255',
            'complete_case_folder' => 'nullable|in:Y,N',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $hearingProcessRecord->update($request->all());

        return redirect()->route('hearing-process.index')
            ->with('success', 'Hearing process updated successfully.')
            ->with('active_tab', 'hearing');
    }

    /**
     * Remove the specified hearing process from storage.
     */
    public function destroy($id)
    {
        try {
            $hearingProcessRecord = HearingProcess::findOrFail($id);
            $hearingProcessRecord->delete();
            Log::info('Hearing Process ID: ' . $id . ' deleted successfully.');

            return redirect()->route('hearing-process.index')
                ->with('success', 'Hearing process deleted successfully.')
                ->with('active_tab', 'hearing');
        } catch (\Exception $e) {
            Log::error('Error deleting hearing process ID: ' . $id . ' - ' . $e->getMessage());
            return redirect()->route('case.index')
                ->with('error', 'Failed to delete hearing process: ' . $e->getMessage())
                ->with('active_tab', 'hearing');
        }
    }
}