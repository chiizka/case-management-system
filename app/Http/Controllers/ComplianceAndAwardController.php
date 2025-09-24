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

    public function inlineUpdate(Request $request, $id)
{
    // Add debug logging
    Log::info('Compliance and Award inline update request received', [
        'compliance_id' => $id,
        'request_data' => $request->all(),
        'content_type' => $request->header('Content-Type')
    ]);
    
    try {
        $complianceAndAward = ComplianceAndAward::findOrFail($id);
        
        // Get all input data
        $inputData = $request->all();
        
        // Remove empty strings and convert them to null
        $cleanedData = [];
        foreach ($inputData as $key => $value) {
            if ($value === '' || $value === '-') {
                $cleanedData[$key] = null;
            } else {
                // Convert string boolean values to actual booleans for boolean fields
                if (in_array($key, [
                    'first_order_dismissal_cnpc', 
                    'tavable_less_than_10_workers', 
                    'with_deposited_monetary_claims', 
                    'with_order_payment_notice',
                    'updated_ticked_in_mis'
                ])) {
                    $cleanedData[$key] = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                } else {
                    $cleanedData[$key] = $value;
                }
            }
        }
        
        Log::info('Cleaned data for validation', ['cleaned_data' => $cleanedData]);
        
        // Validation rules
        $validator = Validator::make($cleanedData, [
            'compliance_order_monetary_award' => 'nullable|numeric',
            'osh_penalty' => 'nullable|numeric',
            'affected_male' => 'nullable|integer|min:0',
            'affected_female' => 'nullable|integer|min:0',
            'first_order_dismissal_cnpc' => 'nullable|boolean',
            'tavable_less_than_10_workers' => 'nullable|boolean',
            'with_deposited_monetary_claims' => 'nullable|boolean',
            'amount_deposited' => 'nullable|numeric|min:0',
            'with_order_payment_notice' => 'nullable|boolean',
            'status_all_employees_received' => 'nullable|string|max:255',
            'status_case_after_first_order' => 'nullable|string|max:255',
            'date_notice_finality_dismissed' => 'nullable|date',
            'released_date_notice_finality' => 'nullable|date',
            'updated_ticked_in_mis' => 'nullable|boolean',
            'second_order_drafter' => 'nullable|string|max:255',
            'date_received_by_drafter_ct_cnpc' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            Log::warning('Validation failed', [
                'errors' => $validator->errors(),
                'data' => $cleanedData
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Validation failed: ' . $validator->errors()->first(),
                'errors' => $validator->errors()
            ], 422);
        }

        $validatedData = $validator->validated();
        
        Log::info('Validated data', ['validated_data' => $validatedData]);
        
        // Update the compliance and award record
        $complianceAndAward->update($validatedData);
        Log::info('Compliance and Award updated successfully');

        // Refresh the model to get updated values
        $complianceAndAward->refresh();
        
        // Also reload the case relationship
        $complianceAndAward->load('case');

        // Prepare response data with proper formatting
        $responseData = [
            'compliance_order_monetary_award' => $complianceAndAward->compliance_order_monetary_award ?? '-',
            'osh_penalty' => $complianceAndAward->osh_penalty ?? '-',
            'affected_male' => $complianceAndAward->affected_male ?? 0,
            'affected_female' => $complianceAndAward->affected_female ?? 0,
            'first_order_dismissal_cnpc' => $complianceAndAward->first_order_dismissal_cnpc ? '1' : '0',
            'tavable_less_than_10_workers' => $complianceAndAward->tavable_less_than_10_workers ? '1' : '0',
            'with_deposited_monetary_claims' => $complianceAndAward->with_deposited_monetary_claims ? '1' : '0',
            'amount_deposited' => $complianceAndAward->amount_deposited ?? '-',
            'with_order_payment_notice' => $complianceAndAward->with_order_payment_notice ? '1' : '0',
            'status_all_employees_received' => $complianceAndAward->status_all_employees_received ?? '-',
            'status_case_after_first_order' => $complianceAndAward->status_case_after_first_order ?? '-',
            'date_notice_finality_dismissed' => $complianceAndAward->date_notice_finality_dismissed ? 
                \Carbon\Carbon::parse($complianceAndAward->date_notice_finality_dismissed)->format('Y-m-d') : '-',
            'released_date_notice_finality' => $complianceAndAward->released_date_notice_finality ? 
                \Carbon\Carbon::parse($complianceAndAward->released_date_notice_finality)->format('Y-m-d') : '-',
            'updated_ticked_in_mis' => $complianceAndAward->updated_ticked_in_mis ? '1' : '0',
            'second_order_drafter' => $complianceAndAward->second_order_drafter ?? '-',
            'date_received_by_drafter_ct_cnpc' => $complianceAndAward->date_received_by_drafter_ct_cnpc ? 
                \Carbon\Carbon::parse($complianceAndAward->date_received_by_drafter_ct_cnpc)->format('Y-m-d') : '-',
        ];

        return response()->json([
            'success' => true,
            'message' => 'Compliance & Award updated successfully!',
            'data' => $responseData
        ]);

    } catch (\Exception $e) {
        Log::error('Compliance and Award inline update failed: ' . $e->getMessage(), [
            'compliance_id' => $id,
            'request_data' => $request->all(),
            'error' => $e->getTraceAsString()
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Failed to update compliance & award: ' . $e->getMessage()
        ], 500);
    }
}
}
