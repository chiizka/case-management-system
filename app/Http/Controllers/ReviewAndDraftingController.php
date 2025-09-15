<?php

namespace App\Http\Controllers;

use App\Models\CaseFile;
use App\Models\ReviewAndDrafting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class ReviewAndDraftingController extends Controller
{
    /**
     * Display a listing of Review & Drafting in the combined case view.
     */
    public function index()
    {
        $cases = CaseFile::all();
        $reviews = ReviewAndDrafting::with('case')->get();

        // Debug (optional)
        // dd([
        //     'cases_count' => $cases->count(),
        //     'reviews_count' => $reviews->count(),
        //     'reviews_data' => $reviews->toArray()
        // ]);

        return view('frontend.case', compact('cases', 'reviews'));
    }

    /**
     * Show the form for creating a new record (combined view).
     */
    public function create()
    {
        $cases = CaseFile::all();
        $reviews = ReviewAndDrafting::with('case')->get();
        return view('frontend.case', compact('cases', 'reviews'));
    }

    /**
     * Store a newly created record in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'case_id' => 'required|exists:cases,id',
            'draft_order_type' => 'required|string|max:255',
            'applicable_draft_order' => 'required|in:Y,N',
            'po_pct' => 'nullable|integer',
            'aging_po_pct' => 'nullable|integer',
            'status_po_pct' => 'required|in:Pending,Ongoing,Overdue,Completed',
            'date_received_from_po' => 'nullable|date',
            'reviewer_drafter' => 'nullable|string|max:255',
            'date_received_by_reviewer' => 'nullable|date',
            'date_returned_from_drafter' => 'nullable|date',
            'aging_10_days_tssd' => 'nullable|integer',
            'status_reviewer_drafter' => 'required|in:Pending,Ongoing,Returned,Approved,Overdue',
            'draft_order_tssd_reviewer' => 'nullable|string|max:255',
        ]);


        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        ReviewAndDrafting::create($request->all());

        return redirect()->route('review-drafting.index')
            ->with('success', 'Review & Drafting record created successfully.')
            ->with('active_tab', 'review-drafting');
    }

    /**
     * Display the specified record in the combined view.
     */
    public function show($id)
    {
        $review = ReviewAndDrafting::with('case')->findOrFail($id);

        $cases = CaseFile::all();
        $reviews = ReviewAndDrafting::with('case')->get();
        return view('frontend.case', compact('cases', 'reviews', 'review'));
    }

    /**
     * Show the form for editing the specified record in combined view.
     */
    public function edit($id)
    {
        $review = ReviewAndDrafting::with('case')->findOrFail($id);

        $cases = CaseFile::all();
        $reviews = ReviewAndDrafting::with('case')->get();
        return view('frontend.case', compact('cases', 'reviews', 'review'));
    }

    /**
     * Update the specified record in storage.
     */
    public function update(Request $request, $id)
    {
        $review = ReviewAndDrafting::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'case_id' => 'required|exists:cases,id',
            'draft_order_type' => 'required|string|max:255',
            'applicable_draft_order' => 'required|in:Y,N',
            'po_pct' => 'nullable|integer',
            'aging_po_pct' => 'nullable|integer',
            'status_po_pct' => 'required|in:Pending,Ongoing,Overdue,Completed',
            'date_received_from_po' => 'nullable|date',
            'reviewer_drafter' => 'nullable|integer',
            'date_received_by_reviewer' => 'nullable|date',
            'date_returned_from_drafter' => 'nullable|date',
            'aging_10_days_tssd' => 'nullable|integer',
            'status_reviewer_drafter' => 'required|in:Pending,Ongoing,Returned,Approved,Overdue',
            'draft_order_tssd_reviewer' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $review->update($request->all());

        return redirect()->route('review-drafting.index')
            ->with('success', 'Review & Drafting record updated successfully.')
            ->with('active_tab', 'review-drafting');
    }

    /**
     * Remove the specified record from storage.
     */
    public function destroy($id)
    {
        try {
            $review = ReviewAndDrafting::findOrFail($id);
            $review->delete();
            Log::info('Review & Drafting ID: ' . $id . ' deleted successfully.');

            return redirect()->route('case.index')
                ->with('success', 'Review & Drafting record deleted successfully.')
                ->with('active_tab', 'review-drafting');
        } catch (\Exception $e) {
            Log::error('Error deleting Review & Drafting ID: ' . $id . ' - ' . $e->getMessage());
            return redirect()->route('case.index')
                ->with('error', 'Failed to delete Review & Drafting: ' . $e->getMessage())
                ->with('active_tab', 'review-drafting');
        }
    }
}
