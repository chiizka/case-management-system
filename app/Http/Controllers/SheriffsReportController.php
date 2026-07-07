<?php

namespace App\Http\Controllers;

use App\Helpers\ActivityLogger;
use App\Models\CaseFile;
use App\Models\SheriffsReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SheriffsReportController extends Controller
{
    /**
     * Confirm the current sheriff is actually assigned to this case right now.
     * Mirrors the same "current_role + Received" check used elsewhere for sheriff tabs.
     */
    private function assertSheriffOwnsCase(CaseFile $case)
    {
        $user = Auth::user();

        if ($user->isAdmin()) {
            return; // admins bypass the assignment check
        }

        if (!$user->isSheriff()) {
            abort(403, 'Only sheriffs can manage reports for this case.');
        }

        $tracking = $case->documentTracking;

        $isAssignedToThisSheriff = $tracking
            && $tracking->current_role === $user->role
            && $tracking->status === 'Received';

        if (!$isAssignedToThisSheriff) {
            abort(403, 'This case is not currently assigned to you.');
        }
    }

    /**
     * List all report links for a case (via its malsu record), newest first.
     */
    public function index($caseId)
    {
        try {
            $case = CaseFile::with('malsu.sheriffsReports.submittedBy')->findOrFail($caseId);

            if (!$case->malsu) {
                return response()->json([
                    'success'  => true,
                    'reports'  => [],
                ]);
            }

            $reports = $case->malsu->sheriffsReports()
                ->with('submittedBy')
                ->orderByDesc('report_date_submitted')
                ->orderByDesc('id')
                ->get()
                ->map(function ($report) {
                    return [
                        'id'                     => $report->id,
                        'report_month'           => optional($report->report_month)->format('Y-m'),
                        'report_month_label'     => optional($report->report_month)->format('F Y'),
                        'report_date_submitted'  => optional($report->report_date_submitted)->format('Y-m-d'),
                        'report_content'         => $report->report_content,
                        'report_link'            => $report->report_link,
                        'submitted_by'           => $report->submittedBy
                            ? trim($report->submittedBy->fname . ' ' . $report->submittedBy->lname)
                            : null,
                        'created_at'             => optional($report->created_at)->format('M d, Y h:i A'),
                    ];
                });

            return response()->json([
                'success' => true,
                'reports' => $reports,
            ]);

        } catch (\Exception $e) {
            Log::error('Error loading sheriff reports: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load reports.',
            ], 500);
        }
    }

    /**
     * Store a new report link for a case. Sheriffs may add multiple per month.
     */
    public function store(Request $request, $caseId)
    {
        $validated = $request->validate([
            'report_month'          => 'required|date_format:Y-m',
            'report_link'           => 'required|url|max:255',
            'report_content'        => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $case = CaseFile::with('malsu')->findOrFail($caseId);

            $this->assertSheriffOwnsCase($case);

            if (!$case->malsu) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'This case has no MALSU record yet; cannot attach a report.',
                ], 422);
            }

            // report_month comes in as "Y-m" (e.g. "2026-03"); store as first day of month
            $reportMonth = $validated['report_month'] . '-01';

            $report = SheriffsReport::create([
                'malsu_id'               => $case->malsu->id,
                'report_month'           => $reportMonth,
                'report_date_submitted'  => now(),
                'report_content'         => $validated['report_content'] ?? null,
                'report_link'            => $validated['report_link'],
                'submitted_by_user_id'   => Auth::id(),
            ]);

            ActivityLogger::logAction(
                'CREATE',
                'Sheriff Report',
                $case->inspection_id,
                "Sheriff submitted a report link for {$case->establishment_name}",
                [
                    'establishment' => $case->establishment_name,
                    'report_month'  => $reportMonth,
                    'report_link'   => $validated['report_link'],
                ]
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Report link added successfully.',
                'report'  => [
                    'id'                    => $report->id,
                    'report_month_label'    => $report->report_month->format('F Y'),
                    'report_date_submitted' => $report->report_date_submitted->format('Y-m-d'),
                    'report_link'           => $report->report_link,
                    'report_content'        => $report->report_content,
                ],
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors'  => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saving sheriff report: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to save report: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a report link. Only the assigned sheriff (or admin) may delete.
     */
    public function destroy($reportId)
    {
        DB::beginTransaction();
        try {
            $report = SheriffsReport::with('malsu.case')->findOrFail($reportId);
            $case = $report->malsu->case;

            if (!$case) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Related case not found.',
                ], 404);
            }

            $this->assertSheriffOwnsCase($case);

            $report->delete();

            ActivityLogger::logAction(
                'DELETE',
                'Sheriff Report',
                $case->inspection_id,
                "Deleted a sheriff report link for {$case->establishment_name}",
                ['establishment' => $case->establishment_name]
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Report link removed.',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting sheriff report: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete report.',
            ], 500);
        }
    }
}