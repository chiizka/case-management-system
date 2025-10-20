<div class="alert alert-success alert-dismissible fade" role="alert" id="success-alert-tab4" style="display: none;">
    <span id="success-message-tab4"></span>
    <button type="button" class="close" onclick="hideAlert('success-alert-tab4')">
        <span aria-hidden="true">&times;</span>
    </button>
</div>

<div class="alert alert-danger alert-dismissible fade" role="alert" id="error-alert-tab4" style="display: none;">
    <span id="error-message-tab4"></span>
    <button type="button" class="close" onclick="hideAlert('error-alert-tab4')">
        <span aria-hidden="true">&times;</span>
    </button>
</div>

<div class="d-flex justify-content-between align-items-center mb-3 custom-search-container">
    <div class="d-flex align-items-center">
        <label class="mr-2 mb-0" style="font-size: 0.8rem;">Search:</label>
        <input type="search" class="form-control form-control-sm" id="customSearch4" placeholder="Search review & drafting..." style="width: 200px;">
    </div>
</div>

<div class="table-container">
    <table class="table table-bordered compact-table sticky-table" id="dataTable4" width="100%" cellspacing="0">
        <thead>
            <tr>
                <th>Case No.</th>
                <th>Establishment Name</th>
                <th>Draft Order Type</th>
                <th>Applicable Draft Order</th>
                <th>PO PCT</th>
                <th>Aging PO PCT</th>
                <th>Status PO PCT</th>
                <th>Date Received from PO</th>
                <th>Reviewer/Drafter</th>
                <th>Date Received by Reviewer</th>
                <th>Date Returned from Drafter</th>
                <th>Aging 10 Days TSSD</th>
                <th>Status Reviewer/Drafter</th>
                <th>Draft Order TSSD Reviewer</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @if(isset($reviewAndDrafting) && $reviewAndDrafting->count() > 0)
                @foreach($reviewAndDrafting as $review)
                    <tr data-id="{{ $review->id }}">
                        <td class="editable-cell readonly-cell" data-field="inspection_id">{{ $review->case->case_no ?? '-' }}</td>
                        <td class="editable-cell readonly-cell" data-field="establishment_name" title="{{ $review->case->establishment_name ?? '' }}">
                            {{ $review->case ? Str::limit($review->case->establishment_name, 25) : '-' }}
                        </td>
                        <td class="editable-cell" data-field="draft_order_type">{{ $review->draft_order_type ?? '-' }}</td>
                        <td class="editable-cell" data-field="applicable_draft_order" data-type="select">
                            @if($review->applicable_draft_order)
                                <span class="badge badge-{{ $review->applicable_draft_order == 'Y' ? 'success' : 'warning' }}">
                                    {{ $review->applicable_draft_order }}
                                </span>
                            @else
                                -
                            @endif
                        </td>
                        <td class="editable-cell" data-field="po_pct">{{ $review->po_pct ?? '-' }}</td>
                        <td class="editable-cell" data-field="aging_po_pct">{{ $review->aging_po_pct ?? '-' }}</td>
                        <td class="editable-cell" data-field="status_po_pct" data-type="select">
                            @if($review->status_po_pct)
                                <span class="badge badge-{{ $review->status_po_pct == 'Completed' ? 'success' : ($review->status_po_pct == 'Overdue' ? 'danger' : 'warning') }}">
                                    {{ $review->status_po_pct }}
                                </span>
                            @else
                                -
                            @endif
                        </td>
                        <td class="editable-cell" data-field="date_received_from_po" data-type="date">{{ $review->date_received_from_po ? \Carbon\Carbon::parse($review->date_received_from_po)->format('Y-m-d') : '-' }}</td>
                        <td class="editable-cell" data-field="reviewer_drafter">{{ $review->reviewer_drafter ?? '-' }}</td>
                        <td class="editable-cell" data-field="date_received_by_reviewer" data-type="date">{{ $review->date_received_by_reviewer ? \Carbon\Carbon::parse($review->date_received_by_reviewer)->format('Y-m-d') : '-' }}</td>
                        <td class="editable-cell" data-field="date_returned_from_drafter" data-type="date">{{ $review->date_returned_from_drafter ? \Carbon\Carbon::parse($review->date_returned_from_drafter)->format('Y-m-d') : '-' }}</td>
                        <td class="editable-cell" data-field="aging_10_days_tssd">{{ $review->aging_10_days_tssd ?? '-' }}</td>
                        <td class="editable-cell" data-field="status_reviewer_drafter" data-type="select">
                            @if($review->status_reviewer_drafter)
                                <span class="badge badge-{{ $review->status_reviewer_drafter == 'Approved' ? 'success' : ($review->status_reviewer_drafter == 'Overdue' ? 'danger' : ($review->status_reviewer_drafter == 'Returned' ? 'info' : 'warning')) }}">
                                    {{ $review->status_reviewer_drafter }}
                                </span>
                            @else
                                -
                            @endif
                        </td>
                        <td class="editable-cell" data-field="draft_order_tssd_reviewer">{{ $review->draft_order_tssd_reviewer ?? '-' }}</td>
                        <td>
                            <button class="btn btn-warning btn-sm edit-row-btn-review" 
                                    data-review-id="{{ $review->id }}"
                                    title="Edit Row">
                                <i class="fas fa-edit"></i>
                            </button>
                            
                            <button type="button" 
                                    class="btn btn-danger btn-sm delete-btn" 
                                    data-review-id="{{ $review->id }}"
                                    data-establishment="{{ $review->case->establishment_name ?? 'N/A' }}"
                                    title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                            
                            @if($review->case && $review->case->current_stage === '4: Review & Drafting')
                                <button type="button" 
                                        class="btn btn-success btn-sm ml-1 move-to-next-stage-btn" 
                                        data-case-id="{{ $review->case->id }}"
                                        data-case-no="{{ $review->case->case_no ?? 'N/A' }}"
                                        data-establishment="{{ $review->case->establishment_name ?? 'N/A' }}"
                                        data-stage="Review & Drafting"
                                        title="Move to Orders & Disposition">
                                    <i class="fas fa-arrow-right"></i> Next
                                </button>
                            @endif
                        </td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="15" class="text-center">No review & drafting records found.</td>
                </tr>
            @endif
        </tbody>
    </table>
</div>