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
     */
    private function assertSheriffOwnsCase(CaseFile $case)
    {
        $user = Auth::user();

        if ($user->isAdmin()) {
            return;
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
     * List all reports for a case (via its malsu record), newest month first.
     */
    public function index($caseId)
    {
        try {
            $case = CaseFile::with('malsu.sheriffsReports.submittedBy')->findOrFail($caseId);

            if (!$case->malsu) {
                return response()->json([
                    'success' => true,
                    'reports' => [],
                ]);
            }

            $reports = $case->malsu->sheriffsReports()
                ->with('submittedBy')
                ->orderByDesc('report_month')
                ->get()
                ->map(function ($report) {
                    // Consider it "edited" if updated_at is more than a minute past created_at
                    // (avoids flagging the initial save as an "edit" due to timestamp rounding)
                    $wasEdited = $report->updated_at && $report->created_at
                        && $report->updated_at->diffInSeconds($report->created_at) > 60;

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
                        'updated_at'             => optional($report->updated_at)->format('M d, Y h:i A'),
                        'was_edited'             => $wasEdited,
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

    public function indexByMalsu($malsuId)
    {
        if (!Auth::user()->isMalsu() && !Auth::user()->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'Access denied.'], 403);
        }

        try {
            $malsu = \App\Models\Malsu::with('sheriffsReports.submittedBy')->findOrFail($malsuId);

            $reports = $malsu->sheriffsReports()
                ->with('submittedBy')
                ->orderByDesc('report_month')
                ->get()
                ->map(function ($report) {
                    $wasEdited = $report->updated_at && $report->created_at
                        && $report->updated_at->diffInSeconds($report->created_at) > 60;

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
                        'updated_at'             => optional($report->updated_at)->format('M d, Y h:i A'),
                        'was_edited'             => $wasEdited,
                    ];
                });

            return response()->json([
                'success' => true,
                'reports' => $reports,
            ]);

        } catch (\Exception $e) {
            Log::error('Error loading sheriff reports by malsu: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load reports.',
            ], 500);
        }
    }

    /**
     * Create or update a report for a case+month (upsert).
     * One report per case per month — resubmitting the same month edits it.
     */
    public function store(Request $request, $caseId)
    {
        $validated = $request->validate([
            'report_month'   => 'required|date_format:Y-m',
            'report_content' => 'required|string',
            'report_link'    => 'nullable|url|max:255',
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

            $reportMonth = $validated['report_month'] . '-01';

            $report = SheriffsReport::where('malsu_id', $case->malsu->id)
                ->where('report_month', $reportMonth)
                ->first();

            $isNew = !$report;

            if (!$report) {
                $report = new SheriffsReport([
                    'malsu_id'              => $case->malsu->id,
                    'report_month'          => $reportMonth,
                    'report_date_submitted' => now(), // frozen at first submission only
                    'submitted_by_user_id'  => Auth::id(),
                ]);
            }

            $report->report_content = $validated['report_content'];
            $report->report_link    = $validated['report_link'] ?? null;
            $report->save(); // updated_at refreshes automatically on every save

            ActivityLogger::logAction(
                $isNew ? 'CREATE' : 'UPDATE',
                'Sheriff Report',
                $case->inspection_id,
                ($isNew ? 'Sheriff submitted' : 'Sheriff updated') .
                    " a report for {$case->establishment_name} ({$report->report_month->format('F Y')})",
                [
                    'establishment' => $case->establishment_name,
                    'report_month'  => $reportMonth,
                ]
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $isNew ? 'Report submitted successfully.' : 'Report updated successfully.',
                'report'  => [
                    'id'                     => $report->id,
                    'report_month'           => $report->report_month->format('Y-m'),
                    'report_month_label'     => $report->report_month->format('F Y'),
                    'report_date_submitted'  => optional($report->report_date_submitted)->format('Y-m-d'),
                    'report_link'            => $report->report_link,
                    'report_content'         => $report->report_content,
                    'updated_at'             => optional($report->updated_at)->format('M d, Y h:i A'),
                    'is_new'                 => $isNew,
                ],
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors'  => $e->errors(),
            ], 422);

        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            if ($e->getCode() === '23000') {
                return response()->json([
                    'success' => false,
                    'message' => 'A report for this month already exists. Please refresh and try again.',
                ], 409);
            }
            Log::error('DB error saving sheriff report: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to save report.',
            ], 500);

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
     * Delete a report. Only the assigned sheriff (or admin) may delete.
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
                "Deleted a sheriff report for {$case->establishment_name}",
                ['establishment' => $case->establishment_name]
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Report removed.',
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