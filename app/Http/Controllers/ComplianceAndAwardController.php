<?php

namespace App\Http\Controllers;

use App\Models\CaseFile;
use App\Models\ComplianceAndAward;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class ComplianceAndAwardController extends Controller
{
    /**
     * Display a listing of compliance & awards in the combined case view.
     */
    public function index()
    {
        $cases = CaseFile::all();
        $complianceAwards = ComplianceAndAward::with('caseFile')->get();

        // Debug dump (you can remove later)
        dd([
            'cases_count' => $cases->count(),
            'compliance_awards_count' => $complianceAwards->count(),
            'compliance_awards_data' => $complianceAwards->toArray()
        ]);

        return view('frontend.case', compact('cases', 'complianceAwards'));
    }

    /**
     * Show the form for creating a new compliance & award (combined view).
     */
    public function create()
    {
        $cases = CaseFile::all();
        $complianceAwards = ComplianceAndAward::with('caseFile')->get();
        return view('frontend.case', compact('cases', 'complianceAwards'));
    }

    /**
     * Store a newly created compliance & award in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'case_id' => 'required|exists:cases,id',
            'compliance_order_monetary_award' => 'nullable|numeric',
            'osh_penalty' => 'nullable|numeric',
            'affected_male' => 'nullable|integer',
            'affected_female' => 'nullable|integer',
            'first_order_dismissal_cnpc' => 'boolean',
            'tavable_less_than_10_workers' => 'boolean',
            'with_deposited_monetary_claims' => 'boolean',
            'amount_deposited' => 'nullable|numeric',
            'with_order_payment_notice' => 'boolean',
            'status_all_employees_received' => 'nullable|string|max:255',
            'status_case_after_first_order' => 'nullable|string|max:255',
            'date_notice_finality_dismissed' => 'nullable|date',
            'released_date_notice_finality' => 'nullable|date',
            'updated_ticked_in_mis' => 'boolean',
            'second_order_drafter' => 'nullable|string|max:255',
            'date_received_by_drafter_ct_cnpc' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        ComplianceAndAward::create($request->all());

        return redirect()->route('compliance-awards.index')
            ->with('success', 'Compliance & Award created successfully.')
            ->with('active_tab', 'compliance_awards');
    }

    /**
     * Display the specified compliance & award in the combined view.
     */
    public function show($id)
    {
        $complianceAward = ComplianceAndAward::with('caseFile')->findOrFail($id);

        $inspectionId = $complianceAward->inspection_id; // accessor
        $establishmentName = $complianceAward->establishment_name; // accessor

        $cases = CaseFile::all();
        $complianceAwards = ComplianceAndAward::with('caseFile')->get();
        return view('frontend.case', compact('cases', 'complianceAwards', 'complianceAward'));
    }

    /**
     * Show the form for editing the specified compliance & award in combined view.
     */
    public function edit($id)
    {
        $complianceAward = ComplianceAndAward::with('caseFile')->findOrFail($id);

        $inspectionId = $complianceAward->inspection_id;
        $establishmentName = $complianceAward->establishment_name;

        $cases = CaseFile::all();
        $complianceAwards = ComplianceAndAward::with('caseFile')->get();
        return view('frontend.case', compact('cases', 'complianceAwards', 'complianceAward'));
    }

    /**
     * Update the specified compliance & award in storage.
     */
    public function update(Request $request, $id)
    {
        $complianceAward = ComplianceAndAward::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'case_id' => 'required|exists:cases,id',
            'compliance_order_monetary_award' => 'nullable|numeric',
            'osh_penalty' => 'nullable|numeric',
            'affected_male' => 'nullable|integer',
            'affected_female' => 'nullable|integer',
            'first_order_dismissal_cnpc' => 'boolean',
            'tavable_less_than_10_workers' => 'boolean',
            'with_deposited_monetary_claims' => 'boolean',
            'amount_deposited' => 'nullable|numeric',
            'with_order_payment_notice' => 'boolean',
            'status_all_employees_received' => 'nullable|string|max:255',
            'status_case_after_first_order' => 'nullable|string|max:255',
            'date_notice_finality_dismissed' => 'nullable|date',
            'released_date_notice_finality' => 'nullable|date',
            'updated_ticked_in_mis' => 'boolean',
            'second_order_drafter' => 'nullable|string|max:255',
            'date_received_by_drafter_ct_cnpc' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $complianceAward->update($request->all());

        return redirect()->route('compliance-awards.index')
            ->with('success', 'Compliance & Award updated successfully.')
            ->with('active_tab', 'compliance_awards');
    }

    /**
     * Remove the specified compliance & award from storage.
     */
    public function destroy($id)
    {
        try {
            $complianceAward = ComplianceAndAward::findOrFail($id);
            $complianceAward->delete();
            Log::info('Compliance & Award ID: ' . $id . ' deleted successfully.');

            return redirect()->route('case.index')
                ->with('success', 'Compliance & Award deleted successfully.')
                ->with('active_tab', 'compliance_awards');
        } catch (\Exception $e) {
            Log::error('Error deleting Compliance & Award ID: ' . $id . ' - ' . $e->getMessage());
            return redirect()->route('case.index')
                ->with('error', 'Failed to delete Compliance & Award: ' . $e->getMessage())
                ->with('active_tab', 'compliance_awards');
        }
    }
}
