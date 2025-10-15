<?php

namespace App\Http\Controllers;

use App\Models\CaseFile;
use App\Models\Docketing;
use App\Helpers\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class DocketingController extends Controller
{
    /**
     * Display a listing of docketing records.
     */
    public function index()
    {
        ActivityLogger::logAction(
            'VIEW',
            'Docketing',
            null,
            'Viewed docketing list page'
        );

        return redirect()->route('case.index')
            ->with('active_tab', 'docketing');
    }

    public function create()
    {
        return redirect()->route('case.index')
            ->with('active_tab', 'docketing');
    }

    /**
     * Store a newly created docketing record.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'case_id' => 'required|exists:cases,id',
            'pct_for_docketing' => 'nullable|numeric|min:0',
            'date_scheduled_docketed' => 'nullable|date',
            'aging_docket' => 'nullable|numeric|min:0',
            'status_docket' => 'nullable|string|max:255',
            'hearing_officer_mis' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->route('case.index')
                ->withErrors($validator)
                ->withInput()
                ->with('active_tab', 'docketing');
        }

        try {
            $docketing = Docketing::create($request->all());
            $case = $docketing->case;

            ActivityLogger::logAction(
                'CREATE',
                'Docketing',
                $case->inspection_id ?? $docketing->id,
                'Created new docketing record',
                [
                    'establishment' => $case->establishment_name ?? 'Unknown',
                    'status' => $request->status_docket ?? 'Not set',
                    'scheduled_date' => $request->date_scheduled_docketed ?? 'Not set'
                ]
            );

            return redirect()->route('case.index')
                ->with('success', 'Docketing created successfully.')
                ->with('active_tab', 'docketing');
        } catch (\Exception $e) {
            Log::error('Error creating docketing: ' . $e->getMessage());

            return redirect()->route('case.index')
                ->with('error', 'Failed to create docketing: ' . $e->getMessage())
                ->with('active_tab', 'docketing');
        }
    }

    /**
     * Display the specified docketing record.
     */
    public function show($id)
    {
        try {
            $docketing = Docketing::with('case')->findOrFail($id);

            ActivityLogger::logAction(
                'VIEW',
                'Docketing',
                $docketing->case->inspection_id ?? $id,
                'Viewed docketing details',
                [
                    'establishment' => $docketing->case->establishment_name ?? 'Unknown'
                ]
            );

            return redirect()->route('case.index')
                ->with('active_tab', 'docketing')
                ->with('highlighted_id', $id);
        } catch (\Exception $e) {
            return redirect()->route('case.index')
                ->with('error', 'Docketing not found.')
                ->with('active_tab', 'docketing');
        }
    }

    /**
     * Show the form for editing the specified docketing record.
     */
    public function edit($id)
    {
        try {
            $docketing = Docketing::with('case')->findOrFail($id);

            ActivityLogger::logAction(
                'VIEW',
                'Docketing',
                $docketing->case->inspection_id ?? $id,
                'Opened docketing record for editing',
                [
                    'establishment' => $docketing->case->establishment_name ?? 'Unknown'
                ]
            );

            if (request()->expectsJson()) {
                return response()->json([
                    'id' => $docketing->id,
                    'case_id' => $docketing->case_id,
                    'inspection_id' => $docketing->case->inspection_id ?? '',
                    'establishment_name' => $docketing->case->establishment_name ?? '',
                    'pct_for_docketing' => $docketing->pct_for_docketing,
                    'date_scheduled_docketed' => $docketing->date_scheduled_docketed,
                    'aging_docket' => $docketing->aging_docket,
                    'status_docket' => $docketing->status_docket,
                    'hearing_officer_mis' => $docketing->hearing_officer_mis,
                ]);
            }

            return redirect()->route('case.index')
                ->with('active_tab', 'docketing')
                ->with('edit_id', $id);
        } catch (\Exception $e) {
            if (request()->expectsJson()) {
                return response()->json(['error' => 'Docketing not found'], 404);
            }

            return redirect()->route('case.index')
                ->with('error', 'Docketing not found.')
                ->with('active_tab', 'docketing');
        }
    }

    /**
     * Update the specified docketing record.
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'case_id' => 'required|exists:cases,id',
            'pct_for_docketing' => 'nullable|numeric|min:0',
            'date_scheduled_docketed' => 'nullable|date',
            'aging_docket' => 'nullable|numeric|min:0',
            'status_docket' => 'nullable|string|max:255',
            'hearing_officer_mis' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->route('case.index')
                ->withErrors($validator)
                ->withInput()
                ->with('active_tab', 'docketing');
        }

        try {
            $docketing = Docketing::findOrFail($id);
            $originalData = $docketing->toArray();
            $docketing->update($request->all());

            $changes = [];
            foreach ($request->all() as $key => $value) {
                if (isset($originalData[$key]) && $originalData[$key] != $value) {
                    $changes[] = ucfirst(str_replace('_', ' ', $key));
                }
            }

            ActivityLogger::logAction(
                'UPDATE',
                'Docketing',
                $docketing->case->inspection_id ?? $id,
                'Updated docketing record',
                [
                    'establishment' => $docketing->case->establishment_name ?? 'Unknown',
                    'fields_changed' => !empty($changes) ? implode(', ', $changes) : 'No changes detected',
                    'change_count' => count($changes)
                ]
            );

            return redirect()->route('case.index')
                ->with('success', 'Docketing updated successfully.')
                ->with('active_tab', 'docketing');
        } catch (\Exception $e) {
            Log::error('Error updating docketing ID: ' . $id . ' - ' . $e->getMessage());

            return redirect()->route('case.index')
                ->with('error', 'Failed to update docketing: ' . $e->getMessage())
                ->with('active_tab', 'docketing');
        }
    }

    /**
     * Remove the specified docketing record.
     */
    public function destroy($id)
    {
        try {
            $docketing = Docketing::with('case')->findOrFail($id);
            $docketingId = $docketing->case->inspection_id ?? $id;
            $establishment = $docketing->case->establishment_name ?? 'Unknown';

            $docketing->delete();

            ActivityLogger::logAction(
                'DELETE',
                'Docketing',
                $docketingId,
                'Deleted docketing record',
                [
                    'establishment' => $establishment
                ]
            );

            Log::info('Docketing ID: ' . $id . ' deleted successfully.');

            return redirect()->route('case.index')
                ->with('success', 'Docketing deleted successfully.')
                ->with('active_tab', 'docketing');
        } catch (\Exception $e) {
            Log::error('Error deleting docketing ID: ' . $id . ' - ' . $e->getMessage());

            return redirect()->route('case.index')
                ->with('error', 'Failed to delete docketing: ' . $e->getMessage())
                ->with('active_tab', 'docketing');
        }
    }

    /**
     * Inline update handler for AJAX.
     */
    public function inlineUpdate(Request $request, $id)
    {
        Log::info('Docketing inline update received', ['id' => $id, 'data' => $request->all()]);

        try {
            $docketing = Docketing::with('case')->findOrFail($id);
            $originalData = $docketing->toArray();
            $originalCase = $docketing->case ? $docketing->case->toArray() : [];

            $inputData = $request->all();
            foreach ($inputData as $key => $value) {
                $inputData[$key] = ($value === '' || $value === '-') ? null : $value;
            }

            $validator = Validator::make($inputData, [
                'pct_for_docketing' => 'nullable|numeric|min:0',
                'date_scheduled_docketed' => 'nullable|date',
                'aging_docket' => 'nullable|numeric|min:0',
                'status_docket' => 'nullable|string|max:255',
                'hearing_officer_mis' => 'nullable|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed: ' . $validator->errors()->first(),
                    'errors' => $validator->errors()
                ], 422);
            }

            $validatedData = $validator->validated();
            $docketing->update($validatedData);
            $docketing->refresh()->load('case');

            $changeDetails = [];
            foreach ($validatedData as $field => $newValue) {
                $oldValue = $originalData[$field] ?? null;
                if ($oldValue != $newValue) {
                    $fieldLabel = ucfirst(str_replace('_', ' ', $field));
                    $oldDisplay = $oldValue ?? 'empty';
                    $newDisplay = $newValue ?? 'empty';

                    if (in_array($field, ['date_scheduled_docketed'])) {
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
                    'Docketing',
                    $docketing->case->inspection_id ?? $id,
                    $logDetails,
                    [
                        'establishment' => $docketing->case->establishment_name ?? 'Unknown',
                        'fields_count' => count($changeDetails),
                        'method' => 'inline_edit'
                    ]
                );
            }

            return response()->json([
                'success' => true,
                'message' => 'Docketing updated successfully!',
                'data' => $docketing
            ]);
        } catch (\Exception $e) {
            Log::error('Inline update failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update docketing: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get docketing data via AJAX.
     */
    public function getDocketing($id)
    {
        try {
            $docketing = Docketing::with('case')->findOrFail($id);

            ActivityLogger::logAction(
                'VIEW',
                'Docketing',
                $docketing->case->inspection_id ?? $id,
                'Retrieved docketing data via API',
                [
                    'establishment' => $docketing->case->establishment_name ?? 'Unknown'
                ]
            );

            return response()->json([
                'success' => true,
                'data' => $docketing
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Docketing not found'
            ], 404);
        }
    }
}
