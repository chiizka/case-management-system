<?php

namespace App\Http\Controllers;

use App\Models\CaseFile;
use App\Models\ComplianceAndAward;
use App\Helpers\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ComplianceAndAwardController extends Controller
{
    /**
     * Display a listing of compliance & awards in the combined case view.
     */
    public function index()
    {
        ActivityLogger::logAction(
            'VIEW',
            'Compliance & Award',
            null,
            'Viewed compliance & awards list page'
        );

        $cases = CaseFile::all();
        $complianceAwards = ComplianceAndAward::with('caseFile')->get();

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

        try {
            $complianceAndAward = ComplianceAndAward::create($request->all());
            $case = $complianceAndAward->case;

            ActivityLogger::logAction(
                'CREATE',
                'Compliance & Award',
                $case->inspection_id ?? $complianceAndAward->id,
                'Created new compliance & award record',
                [
                    'establishment' => $case->establishment_name ?? 'Unknown',
                    'compliance_award' => $request->compliance_order_monetary_award ?? 'Not set',
                    'osh_penalty' => $request->osh_penalty ?? 'Not set'
                ]
            );

            return redirect()->route('compliance-awards.index')
                ->with('success', 'Compliance & Award created successfully.')
                ->with('active_tab', 'compliance_awards');
        } catch (\Exception $e) {
            Log::error('Error creating compliance & award: ' . $e->getMessage());

            return redirect()->route('compliance-awards.index')
                ->with('error', 'Failed to create compliance & award: ' . $e->getMessage())
                ->with('active_tab', 'compliance_awards');
        }
    }

    /**
     * Display the specified compliance & award in the combined view.
     */
    public function show($id)
    {
        try {
            $complianceAward = ComplianceAndAward::with('caseFile')->findOrFail($id);

            ActivityLogger::logAction(
                'VIEW',
                'Compliance & Award',
                $complianceAward->case->inspection_id ?? $id,
                'Viewed compliance & award details',
                [
                    'establishment' => $complianceAward->case->establishment_name ?? 'Unknown'
                ]
            );

            $inspectionId = $complianceAward->inspection_id;
            $establishmentName = $complianceAward->establishment_name;

            $cases = CaseFile::all();
            $complianceAwards = ComplianceAndAward::with('caseFile')->get();
            return view('frontend.case', compact('cases', 'complianceAwards', 'complianceAward'));
        } catch (\Exception $e) {
            return redirect()->route('case.index')
                ->with('error', 'Compliance & Award not found.')
                ->with('active_tab', 'compliance_awards');
        }
    }

    /**
     * Show the form for editing the specified compliance & award in combined view.
     */
    public function edit($id)
    {
        try {
            $complianceAward = ComplianceAndAward::with('caseFile')->findOrFail($id);

            ActivityLogger::logAction(
                'VIEW',
                'Compliance & Award',
                $complianceAward->case->inspection_id ?? $id,
                'Opened compliance & award record for editing',
                [
                    'establishment' => $complianceAward->case->establishment_name ?? 'Unknown'
                ]
            );

            if (request()->expectsJson()) {
                return response()->json([
                    'id' => $complianceAward->id,
                    'case_id' => $complianceAward->case_id,
                    'inspection_id' => $complianceAward->case->inspection_id ?? '',
                    'establishment_name' => $complianceAward->case->establishment_name ?? '',
                    'compliance_order_monetary_award' => $complianceAward->compliance_order_monetary_award,
                    'osh_penalty' => $complianceAward->osh_penalty,
                    'affected_male' => $complianceAward->affected_male,
                    'affected_female' => $complianceAward->affected_female,
                    'first_order_dismissal_cnpc' => $complianceAward->first_order_dismissal_cnpc,
                    'tavable_less_than_10_workers' => $complianceAward->tavable_less_than_10_workers,
                    'with_deposited_monetary_claims' => $complianceAward->with_deposited_monetary_claims,
                    'amount_deposited' => $complianceAward->amount_deposited,
                    'with_order_payment_notice' => $complianceAward->with_order_payment_notice,
                    'status_all_employees_received' => $complianceAward->status_all_employees_received,
                    'status_case_after_first_order' => $complianceAward->status_case_after_first_order,
                    'date_notice_finality_dismissed' => $complianceAward->date_notice_finality_dismissed,
                    'released_date_notice_finality' => $complianceAward->released_date_notice_finality,
                    'updated_ticked_in_mis' => $complianceAward->updated_ticked_in_mis,
                    'second_order_drafter' => $complianceAward->second_order_drafter,
                    'date_received_by_drafter_ct_cnpc' => $complianceAward->date_received_by_drafter_ct_cnpc,
                ]);
            }

            $inspectionId = $complianceAward->inspection_id;
            $establishmentName = $complianceAward->establishment_name;

            $cases = CaseFile::all();
            $complianceAwards = ComplianceAndAward::with('caseFile')->get();
            return view('frontend.case', compact('cases', 'complianceAwards', 'complianceAward'));
        } catch (\Exception $e) {
            if (request()->expectsJson()) {
                return response()->json(['error' => 'Compliance & Award not found'], 404);
            }

            return redirect()->route('case.index')
                ->with('error', 'Compliance & Award not found.')
                ->with('active_tab', 'compliance_awards');
        }
    }

    /**
     * Update the specified compliance & award in storage.
     */
    public function update(Request $request, $id)
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

        try {
            $complianceAndAward = ComplianceAndAward::findOrFail($id);
            $originalData = $complianceAndAward->toArray();
            $complianceAndAward->update($request->all());

            $changes = [];
            foreach ($request->all() as $key => $value) {
                if (isset($originalData[$key]) && $originalData[$key] != $value) {
                    $changes[] = ucfirst(str_replace('_', ' ', $key));
                }
            }

            ActivityLogger::logAction(
                'UPDATE',
                'Compliance & Award',
                $complianceAndAward->case->inspection_id ?? $id,
                'Updated compliance & award record',
                [
                    'establishment' => $complianceAndAward->case->establishment_name ?? 'Unknown',
                    'fields_changed' => !empty($changes) ? implode(', ', $changes) : 'No changes detected',
                    'change_count' => count($changes)
                ]
            );

            return redirect()->route('compliance-awards.index')
                ->with('success', 'Compliance & Award updated successfully.')
                ->with('active_tab', 'compliance_awards');
        } catch (\Exception $e) {
            Log::error('Error updating compliance & award ID: ' . $id . ' - ' . $e->getMessage());

            return redirect()->route('compliance-awards.index')
                ->with('error', 'Failed to update compliance & award: ' . $e->getMessage())
                ->with('active_tab', 'compliance_awards');
        }
    }

    /**
     * Remove the specified compliance & award from storage.
     */
    public function destroy($id)
    {
        try {
            $complianceAndAward = ComplianceAndAward::with('case')->findOrFail($id);
            $complianceAwardId = $complianceAndAward->case->inspection_id ?? $id;
            $establishment = $complianceAndAward->case->establishment_name ?? 'Unknown';

            $complianceAndAward->delete();

            ActivityLogger::logAction(
                'DELETE',
                'Compliance & Award',
                $complianceAwardId,
                'Deleted compliance & award record',
                [
                    'establishment' => $establishment
                ]
            );

            Log::info('Compliance & Award ID: ' . $id . ' deleted successfully.');

            return redirect()->route('case.index')
                ->with('success', 'Compliance & Award deleted successfully.')
                ->with('active_tab', 'compliance_awards');
        } catch (\Exception $e) {
            Log::error('Error deleting compliance & award ID: ' . $id . ' - ' . $e->getMessage());

            return redirect()->route('case.index')
                ->with('error', 'Failed to delete compliance & award: ' . $e->getMessage())
                ->with('active_tab', 'compliance_awards');
        }
    }

    /**
     * Inline update handler for AJAX.
     */
    public function inlineUpdate(Request $request, $id)
    {
        Log::info('Compliance & Award inline update received', ['id' => $id, 'data' => $request->all()]);

        try {
            $complianceAndAward = ComplianceAndAward::with('case')->findOrFail($id);
            $originalData = $complianceAndAward->toArray();
            $originalCase = $complianceAndAward->case ? $complianceAndAward->case->toArray() : [];

            $inputData = $request->all();
            foreach ($inputData as $key => $value) {
                $inputData[$key] = ($value === '' || $value === '-') ? null : $value;
            }

            $validator = Validator::make($inputData, [
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
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed: ' . $validator->errors()->first(),
                    'errors' => $validator->errors()
                ], 422);
            }

            $validatedData = $validator->validated();
            $complianceAndAward->update($validatedData);
            $complianceAndAward->refresh()->load('case');

            $changeDetails = [];
            foreach ($validatedData as $field => $newValue) {
                $oldValue = $originalData[$field] ?? null;
                if ($oldValue != $newValue) {
                    $fieldLabel = ucfirst(str_replace('_', ' ', $field));
                    $oldDisplay = $oldValue ?? 'empty';
                    $newDisplay = $newValue ?? 'empty';

                    if (in_array($field, ['date_notice_finality_dismissed', 'released_date_notice_finality', 'date_received_by_drafter_ct_cnpc'])) {
                        $oldDisplay = $oldValue ? Carbon::parse($oldValue)->format('M d, Y') : 'not set';
                        $newDisplay = $newValue ? Carbon::parse($newValue)->format('M d, Y') : 'not set';
                    }

                    $changeDetails[] = "{$fieldLabel}: '{$oldDisplay}' → '{$newDisplay}'";
                }
            }

            if (!empty($changeDetails)) {
                $logDetails = 'Updated: ' . implode('; ', $changeDetails);
                ActivityLogger::logAction(
                    'UPDATE',
                    'Compliance & Award',
                    $complianceAndAward->case->inspection_id ?? $id,
                    $logDetails,
                    [
                        'establishment' => $complianceAndAward->case->establishment_name ?? 'Unknown',
                        'fields_count' => count($changeDetails),
                        'method' => 'inline_edit'
                    ]
                );
            }

            return response()->json([
                'success' => true,
                'message' => 'Compliance & Award updated successfully!',
                'data' => $complianceAndAward
            ]);
        } catch (\Exception $e) {
            Log::error('Inline update failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update compliance & award: ' . $e->getMessage()
            ], 500);
        }
    }
}