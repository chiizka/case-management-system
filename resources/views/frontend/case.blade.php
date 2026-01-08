@extends('frontend.layouts.app')
@section('content')

<style>
/* Table container for horizontal and vertical scrolling */
.table-container {
    overflow-x: auto;
    overflow-y: auto;
    max-width: 100%;
    max-height: 500px; /* Adjust as needed for vertical scrolling */
    border: 1px solid #dee2e6;
    border-radius: 0.25rem;
    position: relative;
}

/* Smaller text and compact spacing */
.compact-table {
    font-size: 1rem;
}

.compact-table th,
.compact-table td {
    padding: 0.25rem 0.5rem;
    vertical-align: middle;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    box-shadow: inset -1px 0 0 #dee2e6; /* Prevent gaps */
}

.compact-table .btn {
    font-size: 0.65rem;
    padding: 0.2rem 0.4rem;
    margin: 0 0.1rem;
}

.compact-table .badge {
    font-size: 0.65rem;
    padding: 0.2rem 0.4rem;
}

/* Custom search styling */
.custom-search-container {
    margin-bottom: 1rem;
}

.custom-search-container input {
    font-size: 0.8rem;
}

/* Ensure DataTables wrapper doesn't interfere */
.dataTables_wrapper {
    position: relative;
    overflow: visible !important; /* Prevent DataTables from overriding container overflow */
}

/* Inline editing styles */
.editable-cell {
    cursor: pointer;
    min-height: 20px;
    position: relative;
}

.editable-cell:hover:not(.edit-mode) {
    background-color: #f8f9fa;
}

.edit-input {
    border: 2px solid #007bff;
    border-radius: 4px;
    padding: 2px 5px;
    width: 100%;
    font-size: 0.85rem;
    background-color: white;
}

.edit-mode {
    background-color: #e3f2fd !important;
}

.save-cancel-buttons {
    white-space: nowrap;
}

/* Make establishment name column wider */
.table th:nth-child(2),
.table td:nth-child(2) {
    min-width: 200px;
    max-width: 250px;
}

/* Date columns styling */
.table th:nth-child(6),
.table th:nth-child(7),
.table th:nth-child(8),
.table td:nth-child(6),
.table td:nth-child(7),
.table td:nth-child(8) {
    min-width: 110px;
}

/* Actions column */
.table th:last-child,
.table td:last-child {
    min-width: 180px;
}

.readonly-cell {
    background-color: #f8f9fa !important;
    color: #6c757d;
    cursor: not-allowed;
}

.readonly-cell:hover {
    background-color: #e9ecef !important;
}

.tab-loading {
    text-align: center;
    padding: 3rem;
}
</style>

<!-- Main Content -->
<div id="content">
    <!-- Begin Page Content -->
    <div class="container-fluid">
        <!-- Tabs Navigation -->
        <ul class="nav nav-tabs" id="dataTableTabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="tab0-tab" data-toggle="tab" href="#tab0" role="tab" aria-controls="tab0" aria-selected="true">
                    All Active Cases
                </a>
            </li>
            
            @if(in_array(1, $allowedTabs))
            <li class="nav-item">
                <a class="nav-link" id="tab1-tab" data-toggle="tab" href="#tab1" role="tab" aria-controls="tab1" aria-selected="false">
                    Inspection
                </a>
            </li>
            @endif
            
            @if(in_array(2, $allowedTabs))
            <li class="nav-item">
                <a class="nav-link" id="tab2-tab" data-toggle="tab" href="#tab2" role="tab" aria-controls="tab2" aria-selected="false">
                    Docketing
                </a>
            </li>
            @endif
            
            @if(in_array(3, $allowedTabs))
            <li class="nav-item">
                <a class="nav-link" id="tab3-tab" data-toggle="tab" href="#tab3" role="tab" aria-controls="tab3" aria-selected="false">
                    Hearing Process
                </a>
            </li>
            @endif
            
            @if(in_array(4, $allowedTabs))
            <li class="nav-item">
                <a class="nav-link" id="tab4-tab" data-toggle="tab" href="#tab4" role="tab" aria-controls="tab4" aria-selected="false">
                    Review & Drafting
                </a>
            </li>
            @endif
            
            @if(in_array(5, $allowedTabs))
            <li class="nav-item">
                <a class="nav-link" id="tab5-tab" data-toggle="tab" href="#tab5" role="tab" aria-controls="tab5" aria-selected="false">
                    Orders & Disposition
                </a>
            </li>
            @endif
            
            @if(in_array(6, $allowedTabs))
            <li class="nav-item">
                <a class="nav-link" id="tab6-tab" data-toggle="tab" href="#tab6" role="tab" aria-controls="tab6" aria-selected="false">
                    Compliance & Awards
                </a>
            </li>
            @endif
            
            @if(in_array(7, $allowedTabs))
            <li class="nav-item">
                <a class="nav-link" id="tab7-tab" data-toggle="tab" href="#tab7" role="tab" aria-controls="tab7" aria-selected="false">
                    Appeals & Resolution
                </a>
            </li>
            @endif
        </ul>

        <!-- Tabs Content -->
        <div class="tab-content mt-3" id="dataTableTabsContent">
            
        <!-- Tabs Content -->
<div class="tab-content mt-3" id="dataTableTabsContent">
    
    <!-- Tab 0: All Active Cases (Enhanced with corrected columns) -->
    <div class="tab-pane fade show active" id="tab0" role="tabpanel" aria-labelledby="tab0-tab">
        <div class="card shadow mb-4">
            <div class="card-body">
                <!-- Success/Error alerts for AJAX -->
                <div class="alert alert-success alert-dismissible fade" role="alert" id="success-alert-tab0" style="display: none;">
                    <span id="success-message-tab0"></span>
                    <button type="button" class="close" onclick="hideAlert('success-alert-tab0')">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                
                <div class="alert alert-danger alert-dismissible fade" role="alert" id="error-alert-tab0" style="display: none;">
                    <span id="error-message-tab0"></span>
                    <button type="button" class="close" onclick="hideAlert('error-alert-tab0')">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <!-- Search + Buttons Row -->
                <div class="d-flex justify-content-between align-items-center mb-3 custom-search-container">
                    <div class="d-flex align-items-center">
                        <label class="mr-2 mb-0" style="font-size: 0.8rem;">Search:</label>
                        <input type="search" class="form-control form-control-sm" id="customSearch0" placeholder="Search all active cases..." style="width: 200px;">
                    </div>
                    <div>
                        <!-- NEW: Add this Upload CSV button -->
                        <button class="btn btn-success btn-sm mr-2" data-toggle="modal" data-target="#uploadCsvModal">
                            <i class="fas fa-upload"></i> Upload CSV
                        </button>
                        
                        <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addCaseModal" data-mode="add">
                            + Add Case
                        </button>
                    </div>
                </div>
                
                <!-- Table Container -->
                <div class="table-container">
                    <table class="table table-bordered compact-table sticky-table" id="dataTable0" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <!-- Core Information -->
                                <th>No.</th>
                                <th>Inspection ID</th>
                                <th>Case No.</th>
                                <th>Establishment Name</th>
                                <th>Establishment Address</th>
                                <th>Mode</th>
                                <th>PO </th>
                                <th>Current Stage</th>
                                <th>Overall Status</th>
                                
                                <!-- Inspection Stage -->
                                <th>Date of Inspection</th>
                                <th>Inspector Name</th>
                                <th>Inspector Authority No.</th>
                                <th>Date of NR</th>
                                <th>Lapse 20 Day Period</th>
                                
                                <!-- Docketing Stage -->
                                <th>PCT for Docketing</th>
                                <th>Date Scheduled Docketed</th>
                                <th>Aging Docket</th>
                                <th>Status Docket</th>
                                <th>Hearing Officer (MIS)</th>
                                
                                <!-- Hearing Process Stage -->
                                <th>Date 1st MC (Actual)</th>
                                <th>First MC PCT</th>
                                <th>Status 1st MC</th>
                                <th>Date 2nd/Last MC</th>
                                <th>Second/Last MC PCT</th>
                                <th>Status 2nd MC</th>
                                <th>Case Folder Forwarded to RO</th>
                                <th>Draft Order from PO Type</th>
                                <th>Applicable Draft Order</th>
                                <th>Complete Case Folder</th>
                                <th>TWG ALI</th>
                                
                                <!-- Review & Drafting Stage -->
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
                                <th>Final Review Date Received</th>
                                <th>Date Received Drafter Finalization</th>
                                <th>Date Returned Case Mgmt Signature</th>
                                <th>Aging 2 Days Finalization</th>
                                <th>Status Finalization</th>
                                
                                <!-- Orders & Disposition Stage -->
                                <th>PCT 96 Days</th>
                                <th>Date Signed (MIS)</th>
                                <th>Status PCT</th>
                                <th>Reference Date PCT</th>
                                <th>Aging PCT</th>
                                <th>Disposition (MIS)</th>
                                <th>Disposition (Actual)</th>
                                <th>Findings to Comply</th>
                                <th>Compliance Order Monetary Award</th>
                                <th>OSH Penalty</th>
                                <th>Affected Male</th>
                                <th>Affected Female</th>
                                <th>Date of Order (Actual)</th>
                                <th>Released Date (Actual)</th>
                                
                                <!-- Compliance & Awards Stage -->
                                <th>First Order Dismissal CNPC</th>
                                <th>Tavable Less Than 10 Workers</th>
                                <th>Scanned Order First</th>
                                <th>With Deposited Monetary Claims</th>
                                <th>Amount Deposited</th>
                                <th>With Order Payment Notice</th>
                                <th>Status All Employees Received</th>
                                <th>Status Case After First Order</th>
                                <th>Date Notice Finality Dismissed</th>
                                <th>Released Date Notice Finality</th>
                                <th>Scanned Notice Finality</th>
                                <th>Updated Ticked in MIS</th>
                                
                                <!-- Appeals & Resolution Stage (2nd Order) -->
                                <th>Second Order Drafter</th>
                                <th>Date Received by Drafter CT CNPC</th>
                                <th>Date Returned Case Mgmt CT CNPC</th>
                                <th>Review CT CNPC</th>
                                <th>Date Received Drafter Finalization 2nd</th>
                                <th>Date Returned Case Mgmt Signature 2nd</th>
                                <th>Date Order 2nd CNPC</th>
                                <th>Released Date 2nd CNPC</th>
                                <th>Scanned Order 2nd CNPC</th>
                                
                                <!-- Appeals & Resolution Stage (MALSU) -->
                                <th>Date Forwarded MALSU</th>
                                <th>Scanned Indorsement MALSU</th>
                                <th>Motion Reconsideration Date</th>
                                <th>Date Received MALSU</th>
                                <th>Date Resolution MR</th>
                                <th>Released Date Resolution MR</th>
                                <th>Scanned Resolution MR</th>
                                <th>Date Appeal Received Records</th>
                                <th>Date Indorsed Office Secretary</th>
                                
                                <!-- Additional Information -->
                                <th>Logbook Page Number</th>
                                <th>Remarks/Notes</th>
                                
                                <th>Created At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(isset($cases) && $cases->count() > 0)
                                @foreach($cases as $case)
                                    <tr data-id="{{ $case->id }}">
                                        <!-- Core Information -->
                                        <td class="editable-cell" data-field="no">{{ $case->no ?? '-' }}</td>
                                        <td class="editable-cell" data-field="inspection_id">{{ $case->inspection_id ?? '-' }}</td>
                                        <td class="editable-cell" data-field="case_no">{{ $case->case_no ?? '-' }}</td>
                                        <td class="editable-cell" data-field="establishment_name" title="{{ $case->establishment_name ?? '' }}">
                                            {{ $case->establishment_name ? Str::limit($case->establishment_name, 25) : '-' }}
                                        </td>
                                        <td class="editable-cell" data-field="establishment_address" title="{{ $case->establishment_address ?? '' }}">
                                            {{ $case->establishment_address ? Str::limit($case->establishment_address, 30) : '-' }}
                                        </td>
                                        <td class="editable-cell" data-field="mode">{{ $case->mode ?? '-' }}</td>
                                        <td class="editable-cell" data-field="po_office">{{ $case->po_office ?? '-' }}</td>
                                        <td class="editable-cell" data-field="current_stage" data-type="select">
                                            {{ explode(': ', $case->current_stage)[1] ?? $case->current_stage ?? '-' }}
                                        </td>
                                        <td class="editable-cell" data-field="overall_status" data-type="select">
                                            {{ $case->overall_status ?? '-' }}
                                        </td>
                                        
                                        <!-- Inspection Stage -->
                                        <td class="editable-cell" data-field="date_of_inspection" data-type="date">
                                            {{ $case->date_of_inspection ? \Carbon\Carbon::parse($case->date_of_inspection)->format('Y-m-d') : '-' }}
                                        </td>
                                        <td class="editable-cell" data-field="inspector_name" title="{{ $case->inspector_name ?? '' }}">
                                            {{ $case->inspector_name ? Str::limit($case->inspector_name, 20) : '-' }}
                                        </td>
                                        <td class="editable-cell" data-field="inspector_authority_no">{{ $case->inspector_authority_no ?? '-' }}</td>
                                        <td class="editable-cell" data-field="date_of_nr" data-type="date">
                                            {{ $case->date_of_nr ? \Carbon\Carbon::parse($case->date_of_nr)->format('Y-m-d') : '-' }}
                                        </td>
                                        <td class="editable-cell" data-field="lapse_20_day_period">{{ $case->lapse_20_day_period ?? '-' }}</td>
                                        
                                        <!-- Docketing Stage -->
                                        <td class="editable-cell" data-field="pct_for_docketing">{{ $case->pct_for_docketing ?? '-' }}</td>
                                        <td class="editable-cell" data-field="date_scheduled_docketed" data-type="date">
                                            {{ $case->date_scheduled_docketed ? \Carbon\Carbon::parse($case->date_scheduled_docketed)->format('Y-m-d') : '-' }}
                                        </td>
                                        <td class="editable-cell" data-field="aging_docket">{{ $case->aging_docket ?? '-' }}</td>
                                        <td class="editable-cell" data-field="status_docket">{{ $case->status_docket ?? '-' }}</td>
                                        <td class="editable-cell" data-field="hearing_officer_mis" title="{{ $case->hearing_officer_mis ?? '' }}">
                                            {{ $case->hearing_officer_mis ? Str::limit($case->hearing_officer_mis, 20) : '-' }}
                                        </td>
                                        
                                        <!-- Hearing Process Stage -->
                                        <td class="editable-cell" data-field="date_1st_mc_actual" data-type="date">
                                            {{ $case->date_1st_mc_actual ? \Carbon\Carbon::parse($case->date_1st_mc_actual)->format('Y-m-d') : '-' }}
                                        </td>
                                        <td class="editable-cell" data-field="first_mc_pct">{{ $case->first_mc_pct ?? '-' }}</td>
                                        <td class="editable-cell" data-field="status_1st_mc">{{ $case->status_1st_mc ?? '-' }}</td>
                                        <td class="editable-cell" data-field="date_2nd_last_mc" data-type="date">
                                            {{ $case->date_2nd_last_mc ? \Carbon\Carbon::parse($case->date_2nd_last_mc)->format('Y-m-d') : '-' }}
                                        </td>
                                        <td class="editable-cell" data-field="second_last_mc_pct">{{ $case->second_last_mc_pct ?? '-' }}</td>
                                        <td class="editable-cell" data-field="status_2nd_mc">{{ $case->status_2nd_mc ?? '-' }}</td>
                                        <td class="editable-cell" data-field="case_folder_forwarded_to_ro">{{ $case->case_folder_forwarded_to_ro ?? '-' }}</td>
                                        <td class="editable-cell" data-field="draft_order_from_po_type">{{ $case->draft_order_from_po_type ?? '-' }}</td>
                                        <td class="editable-cell" data-field="applicable_draft_order">{{ $case->applicable_draft_order ?? '-' }}</td>
                                        <td class="editable-cell" data-field="complete_case_folder">{{ $case->complete_case_folder ?? '-' }}</td>
                                        <td class="editable-cell" data-field="twg_ali">{{ $case->twg_ali ?? '-' }}</td>
                                        
                                        <!-- Review & Drafting Stage -->
                                        <td class="editable-cell" data-field="po_pct">{{ $case->po_pct ?? '-' }}</td>
                                        <td class="editable-cell" data-field="aging_po_pct">{{ $case->aging_po_pct ?? '-' }}</td>
                                        <td class="editable-cell" data-field="status_po_pct">{{ $case->status_po_pct ?? '-' }}</td>
                                        <td class="editable-cell" data-field="date_received_from_po" data-type="date">
                                            {{ $case->date_received_from_po ? \Carbon\Carbon::parse($case->date_received_from_po)->format('Y-m-d') : '-' }}
                                        </td>
                                        <td class="editable-cell" data-field="reviewer_drafter" title="{{ $case->reviewer_drafter ?? '' }}">
                                            {{ $case->reviewer_drafter ? Str::limit($case->reviewer_drafter, 20) : '-' }}
                                        </td>
                                        <td class="editable-cell" data-field="date_received_by_reviewer" data-type="date">
                                            {{ $case->date_received_by_reviewer ? \Carbon\Carbon::parse($case->date_received_by_reviewer)->format('Y-m-d') : '-' }}
                                        </td>
                                        <td class="editable-cell" data-field="date_returned_from_drafter" data-type="date">
                                            {{ $case->date_returned_from_drafter ? \Carbon\Carbon::parse($case->date_returned_from_drafter)->format('Y-m-d') : '-' }}
                                        </td>
                                        <td class="editable-cell" data-field="aging_10_days_tssd">{{ $case->aging_10_days_tssd ?? '-' }}</td>
                                        <td class="editable-cell" data-field="status_reviewer_drafter">{{ $case->status_reviewer_drafter ?? '-' }}</td>
                                        <td class="editable-cell" data-field="draft_order_tssd_reviewer">{{ $case->draft_order_tssd_reviewer ?? '-' }}</td>
                                        <td class="editable-cell" data-field="final_review_date_received" data-type="date">
                                            {{ $case->final_review_date_received ? \Carbon\Carbon::parse($case->final_review_date_received)->format('Y-m-d') : '-' }}
                                        </td>
                                        <td class="editable-cell" data-field="date_received_drafter_finalization" data-type="date">
                                            {{ $case->date_received_drafter_finalization ? \Carbon\Carbon::parse($case->date_received_drafter_finalization)->format('Y-m-d') : '-' }}
                                        </td>
                                        <td class="editable-cell" data-field="date_returned_case_mgmt_signature" data-type="date">
                                            {{ $case->date_returned_case_mgmt_signature ? \Carbon\Carbon::parse($case->date_returned_case_mgmt_signature)->format('Y-m-d') : '-' }}
                                        </td>
                                        <td class="editable-cell" data-field="aging_2_days_finalization">{{ $case->aging_2_days_finalization ?? '-' }}</td>
                                        <td class="editable-cell" data-field="status_finalization">{{ $case->status_finalization ?? '-' }}</td>
                                        
                                        <!-- Orders & Disposition Stage -->
                                        <td class="editable-cell" data-field="pct_96_days">{{ $case->pct_96_days ?? '-' }}</td>
                                        <td class="editable-cell" data-field="date_signed_mis" data-type="date">
                                            {{ $case->date_signed_mis ? \Carbon\Carbon::parse($case->date_signed_mis)->format('Y-m-d') : '-' }}
                                        </td>
                                        <td class="editable-cell" data-field="status_pct">{{ $case->status_pct ?? '-' }}</td>
                                        <td class="editable-cell" data-field="reference_date_pct" data-type="date">
                                            {{ $case->reference_date_pct ? \Carbon\Carbon::parse($case->reference_date_pct)->format('Y-m-d') : '-' }}
                                        </td>
                                        <td class="editable-cell" data-field="aging_pct">{{ $case->aging_pct ?? '-' }}</td>
                                        <td class="editable-cell" data-field="disposition_mis">{{ $case->disposition_mis ?? '-' }}</td>
                                        <td class="editable-cell" data-field="disposition_actual">{{ $case->disposition_actual ?? '-' }}</td>
                                        <td class="editable-cell" data-field="findings_to_comply" title="{{ $case->findings_to_comply ?? '' }}">
                                            {{ $case->findings_to_comply ? Str::limit($case->findings_to_comply, 20) : '-' }}
                                        </td>
                                        <td class="editable-cell" data-field="compliance_order_monetary_award">
                                            {{ $case->compliance_order_monetary_award ? number_format($case->compliance_order_monetary_award, 2) : '-' }}
                                        </td>
                                        <td class="editable-cell" data-field="osh_penalty">
                                            {{ $case->osh_penalty ? number_format($case->osh_penalty, 2) : '-' }}
                                        </td>
                                        <td class="editable-cell" data-field="affected_male">{{ $case->affected_male ?? '-' }}</td>
                                        <td class="editable-cell" data-field="affected_female">{{ $case->affected_female ?? '-' }}</td>
                                        <td class="editable-cell" data-field="date_of_order_actual" data-type="date">
                                            {{ $case->date_of_order_actual ? \Carbon\Carbon::parse($case->date_of_order_actual)->format('Y-m-d') : '-' }}
                                        </td>
                                        <td class="editable-cell" data-field="released_date_actual" data-type="date">
                                            {{ $case->released_date_actual ? \Carbon\Carbon::parse($case->released_date_actual)->format('Y-m-d') : '-' }}
                                        </td>
                                        
                                        <!-- Compliance & Awards Stage (Boolean fields now show Yes/No) -->
                                        <td class="editable-cell" data-field="first_order_dismissal_cnpc" data-type="boolean">
                                            {{ $case->first_order_dismissal_cnpc ? 'Yes' : 'No' }}
                                        </td>
                                        <td class="editable-cell" data-field="tavable_less_than_10_workers" data-type="boolean">
                                            {{ $case->tavable_less_than_10_workers ? 'Yes' : 'No' }}
                                        </td>
                                        <td class="editable-cell" data-field="scanned_order_first">{{ $case->scanned_order_first ?? '-' }}</td>
                                        <td class="editable-cell" data-field="with_deposited_monetary_claims" data-type="boolean">
                                            {{ $case->with_deposited_monetary_claims ? 'Yes' : 'No' }}
                                        </td>
                                        <td class="editable-cell" data-field="amount_deposited">
                                            {{ $case->amount_deposited ? number_format($case->amount_deposited, 2) : '-' }}
                                        </td>
                                        <td class="editable-cell" data-field="with_order_payment_notice" data-type="boolean">
                                            {{ $case->with_order_payment_notice ? 'Yes' : 'No' }}
                                        </td>
                                        <td class="editable-cell" data-field="status_all_employees_received">{{ $case->status_all_employees_received ?? '-' }}</td>
                                        <td class="editable-cell" data-field="status_case_after_first_order">{{ $case->status_case_after_first_order ?? '-' }}</td>
                                        <td class="editable-cell" data-field="date_notice_finality_dismissed" data-type="date">
                                            {{ $case->date_notice_finality_dismissed ? \Carbon\Carbon::parse($case->date_notice_finality_dismissed)->format('Y-m-d') : '-' }}
                                        </td>
                                        <td class="editable-cell" data-field="released_date_notice_finality" data-type="date">
                                            {{ $case->released_date_notice_finality ? \Carbon\Carbon::parse($case->released_date_notice_finality)->format('Y-m-d') : '-' }}
                                        </td>
                                        <td class="editable-cell" data-field="scanned_notice_finality">{{ $case->scanned_notice_finality ?? '-' }}</td>
                                        <td class="editable-cell" data-field="updated_ticked_in_mis" data-type="boolean">
                                            {{ $case->updated_ticked_in_mis ? 'Yes' : 'No' }}
                                        </td>
                                        
                                        <!-- Appeals & Resolution Stage (2nd Order) -->
                                        <td class="editable-cell" data-field="second_order_drafter" title="{{ $case->second_order_drafter ?? '' }}">
                                            {{ $case->second_order_drafter ? Str::limit($case->second_order_drafter, 20) : '-' }}
                                        </td>
                                        <td class="editable-cell" data-field="date_received_by_drafter_ct_cnpc" data-type="date">
                                            {{ $case->date_received_by_drafter_ct_cnpc ? \Carbon\Carbon::parse($case->date_received_by_drafter_ct_cnpc)->format('Y-m-d') : '-' }}
                                        </td>
                                        <td class="editable-cell" data-field="date_returned_case_mgmt_ct_cnpc" data-type="date">
                                            {{ $case->date_returned_case_mgmt_ct_cnpc ? \Carbon\Carbon::parse($case->date_returned_case_mgmt_ct_cnpc)->format('Y-m-d') : '-' }}
                                        </td>
                                        <td class="editable-cell" data-field="review_ct_cnpc">{{ $case->review_ct_cnpc ?? '-' }}</td>
                                        <td class="editable-cell" data-field="date_received_drafter_finalization_2nd" data-type="date">
                                            {{ $case->date_received_drafter_finalization_2nd ? \Carbon\Carbon::parse($case->date_received_drafter_finalization_2nd)->format('Y-m-d') : '-' }}
                                        </td>
                                        <td class="editable-cell" data-field="date_returned_case_mgmt_signature_2nd" data-type="date">
                                            {{ $case->date_returned_case_mgmt_signature_2nd ? \Carbon\Carbon::parse($case->date_returned_case_mgmt_signature_2nd)->format('Y-m-d') : '-' }}
                                        </td>
                                        <td class="editable-cell" data-field="date_order_2nd_cnpc" data-type="date">
                                            {{ $case->date_order_2nd_cnpc ? \Carbon\Carbon::parse($case->date_order_2nd_cnpc)->format('Y-m-d') : '-' }}
                                        </td>
                                        <td class="editable-cell" data-field="released_date_2nd_cnpc" data-type="date">
                                            {{ $case->released_date_2nd_cnpc ? \Carbon\Carbon::parse($case->released_date_2nd_cnpc)->format('Y-m-d') : '-' }}
                                        </td>
                                        <td class="editable-cell" data-field="scanned_order_2nd_cnpc">{{ $case->scanned_order_2nd_cnpc ?? '-' }}</td>
                                        
                                        <!-- Appeals & Resolution Stage (MALSU) -->
                                        <td class="editable-cell" data-field="date_forwarded_malsu" data-type="date">
                                            {{ $case->date_forwarded_malsu ? \Carbon\Carbon::parse($case->date_forwarded_malsu)->format('Y-m-d') : '-' }}
                                        </td>
                                        <td class="editable-cell" data-field="scanned_indorsement_malsu">{{ $case->scanned_indorsement_malsu ?? '-' }}</td>
                                        <td class="editable-cell" data-field="motion_reconsideration_date" data-type="date">
                                            {{ $case->motion_reconsideration_date ? \Carbon\Carbon::parse($case->motion_reconsideration_date)->format('Y-m-d') : '-' }}
                                        </td>
                                        <td class="editable-cell" data-field="date_received_malsu" data-type="date">
                                            {{ $case->date_received_malsu ? \Carbon\Carbon::parse($case->date_received_malsu)->format('Y-m-d') : '-' }}
                                        </td>
                                        <td class="editable-cell" data-field="date_resolution_mr" data-type="date">
                                            {{ $case->date_resolution_mr ? \Carbon\Carbon::parse($case->date_resolution_mr)->format('Y-m-d') : '-' }}
                                        </td>
                                        <td class="editable-cell" data-field="released_date_resolution_mr" data-type="date">
                                            {{ $case->released_date_resolution_mr ? \Carbon\Carbon::parse($case->released_date_resolution_mr)->format('Y-m-d') : '-' }}
                                        </td>
                                        <td class="editable-cell" data-field="scanned_resolution_mr">{{ $case->scanned_resolution_mr ?? '-' }}</td>
                                        <td class="editable-cell" data-field="date_appeal_received_records" data-type="date">
                                            {{ $case->date_appeal_received_records ? \Carbon\Carbon::parse($case->date_appeal_received_records)->format('Y-m-d') : '-' }}
                                        </td>
                                        <td class="editable-cell" data-field="date_indorsed_office_secretary" data-type="date">
                                            {{ $case->date_indorsed_office_secretary ? \Carbon\Carbon::parse($case->date_indorsed_office_secretary)->format('Y-m-d') : '-' }}
                                        </td>
                                        
                                        <!-- Additional Information -->
                                        <td class="editable-cell" data-field="logbook_page_number">{{ $case->logbook_page_number ?? '-' }}</td>
                                        <td class="editable-cell" data-field="remarks_notes" title="{{ $case->remarks_notes ?? '' }}">
                                            {{ $case->remarks_notes ? Str::limit($case->remarks_notes, 30) : '-' }}
                                        </td>
                                        
                                        <td class="non-editable">
                                            {{ $case->created_at ? \Carbon\Carbon::parse($case->created_at)->format('Y-m-d') : '-' }}
                                        </td>
                                        <td>
                                            <button class="btn btn-warning btn-sm edit-row-btn-case" 
                                                    data-case-id="{{ $case->id }}"
                                                    title="Edit Row">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            
                                            <button type="button" 
                                                    class="btn btn-danger btn-sm delete-btn" 
                                                    data-case-id="{{ $case->id }}"
                                                    data-case-no="{{ $case->case_no ?? 'N/A' }}"
                                                    data-establishment="{{ $case->establishment_name ?? 'N/A' }}"
                                                    title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                            
                                            <button class="btn btn-info btn-sm view-history-btn" 
                                                    data-case-id="{{ $case->id }}"
                                                    data-case-no="{{ $case->case_no ?? 'N/A' }}"
                                                    data-establishment="{{ $case->establishment_name ?? 'N/A' }}"
                                                    title="View Document History">
                                                <i class="fas fa-history"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="100" class="text-center">No cases found. Click "Add Case" to create your first case.</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Tab 1: Inspection (LAZY LOAD) -->
    <div class="tab-pane fade" id="tab1" role="tabpanel" aria-labelledby="tab1-tab">
        <div class="card shadow mb-4">
            <div class="card-body">
                <!-- This will be replaced with actual content via AJAX when tab is clicked -->
                <div class="tab-loading text-center" style="padding: 3rem;">
                    <div class="spinner-border text-primary mb-3" role="status" style="width: 3rem; height: 3rem;">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <p class="text-muted">Loading inspection data...</p>
                    <small class="text-muted">This may take a moment for the first load</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Tab 2: Docketing (LAZY LOAD) -->
    <div class="tab-pane fade" id="tab2" role="tabpanel" aria-labelledby="tab2-tab">
        <div class="card shadow mb-4">
            <div class="card-body">
                <div class="tab-loading text-center" style="padding: 3rem;">
                    <div class="spinner-border text-primary mb-3" role="status" style="width: 3rem; height: 3rem;">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <p class="text-muted">Loading docketing data...</p>
                    <small class="text-muted">This may take a moment for the first load</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Tab 3: Hearing Process (LAZY LOAD) -->
    <div class="tab-pane fade" id="tab3" role="tabpanel" aria-labelledby="tab3-tab">
        <div class="card shadow mb-4">
            <div class="card-body">
                <div class="tab-loading text-center" style="padding: 3rem;">
                    <div class="spinner-border text-primary mb-3" role="status" style="width: 3rem; height: 3rem;">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <p class="text-muted">Loading hearing process data...</p>
                    <small class="text-muted">This may take a moment for the first load</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Tab 4: Review & Drafting (LAZY LOAD) -->
    <div class="tab-pane fade" id="tab4" role="tabpanel" aria-labelledby="tab4-tab">
        <div class="card shadow mb-4">
            <div class="card-body">
                <div class="tab-loading text-center" style="padding: 3rem;">
                    <div class="spinner-border text-primary mb-3" role="status" style="width: 3rem; height: 3rem;">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <p class="text-muted">Loading review & drafting data...</p>
                    <small class="text-muted">This may take a moment for the first load</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Tab 5: Orders & Disposition (LAZY LOAD) -->
    <div class="tab-pane fade" id="tab5" role="tabpanel" aria-labelledby="tab5-tab">
        <div class="card shadow mb-4">
            <div class="card-body">
                <div class="tab-loading text-center" style="padding: 3rem;">
                    <div class="spinner-border text-primary mb-3" role="status" style="width: 3rem; height: 3rem;">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <p class="text-muted">Loading orders & disposition data...</p>
                    <small class="text-muted">This may take a moment for the first load</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Tab 6: Compliance & Awards (LAZY LOAD) -->
    <div class="tab-pane fade" id="tab6" role="tabpanel" aria-labelledby="tab6-tab">
        <div class="card shadow mb-4">
            <div class="card-body">
                <div class="tab-loading text-center" style="padding: 3rem;">
                    <div class="spinner-border text-primary mb-3" role="status" style="width: 3rem; height: 3rem;">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <p class="text-muted">Loading compliance & awards data...</p>
                    <small class="text-muted">This may take a moment for the first load</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Tab 7: Appeals & Resolution (LAZY LOAD) -->
    <div class="tab-pane fade" id="tab7" role="tabpanel" aria-labelledby="tab7-tab">
        <div class="card shadow mb-4">
            <div class="card-body">
                <div class="tab-loading text-center" style="padding: 3rem;">
                    <div class="spinner-border text-primary mb-3" role="status" style="width: 3rem; height: 3rem;">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <p class="text-muted">Loading appeals & resolution data...</p>
                    <small class="text-muted">This may take a moment for the first load</small>
                </div>
            </div>
        </div>
    </div>

        </div>
        <!-- End Tabs Content -->
    </div>
    <!-- /.container-fluid -->
</div>
<!-- End of Main Content -->

    <!-- Modal for Adding/Editing Case Records -->
    <div class="modal fade" id="addCaseModal" tabindex="-1" role="dialog" aria-labelledby="addCaseModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addCaseModalLabel">Add New Case</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="caseForm" method="POST" action="{{ route('case.store') }}">
                        @csrf
                        <input type="hidden" name="_method" id="formMethod" value="POST">

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="inspection_id">Inspection ID</label>
                                    <input type="text" class="form-control" id="inspection_id" name="inspection_id" placeholder="Enter inspection ID" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="case_no">Case No.</label>
                                    <input type="text" class="form-control" id="case_no" name="case_no" placeholder="Enter case number (optional)">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="establishment_name">Establishment Name</label>
                            <input type="text" class="form-control" id="establishment_name" name="establishment_name" placeholder="Enter establishment name" required>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="current_stage">Current Stage</label>
                                    <select class="form-control" id="current_stage" name="current_stage" required disabled>
                                        <option value="1: Inspections" selected>1: Inspections</option>
                                    </select>
                                    <!-- Hidden input to ensure the value is submitted -->
                                    <input type="hidden" name="current_stage" value="1: Inspections">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="overall_status">Overall Status</label>
                                    <select class="form-control" id="overall_status" name="overall_status" required disabled>
                                        <option value="Active" selected>Active</option>
                                        <option value="Completed">Completed</option>
                                        <option value="Dismissed">Dismissed</option>
                                    </select>
                                    <!-- Hidden input to ensure the value is submitted -->
                                    <input type="hidden" name="overall_status" value="Active">
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Save Case</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteCaseModal" tabindex="-1" role="dialog" aria-labelledby="deleteCaseModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="deleteCaseModalLabel">Delete Record</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning" role="alert">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        <strong>Warning!</strong> This action cannot be undone.
                    </div>
                    <p>Are you sure you want to delete this record?</p>
                    <p id="deleteCaseInfo" class="text-muted small mb-0"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                        <i class="fas fa-trash mr-2"></i>Delete
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- CSV/Excel Upload Modal -->
    <div class="modal fade" id="uploadCsvModal" tabindex="-1" role="dialog" aria-labelledby="uploadCsvModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="uploadCsvModalLabel">
                        <i class="fas fa-upload"></i> Upload CSV/Excel File
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="csvUploadForm" enctype="multipart/form-data">
                    <div class="modal-body">
                        @csrf
                        
                        <!-- File Input -->
                        <div class="form-group">
                            <label for="csv_file">Select CSV or Excel File <span class="text-danger">*</span></label>
                            <div class="custom-file">
                                <input type="file" 
                                    class="custom-file-input" 
                                    id="csv_file" 
                                    name="csv_file" 
                                    accept=".csv,.xlsx,.xls" 
                                    required>
                                <label class="custom-file-label" for="csv_file">Choose file...</label>
                            </div>
                            <small class="form-text text-muted">
                                <i class="fas fa-info-circle"></i> Accepts CSV (.csv) or Excel (.xlsx, .xls) files. Maximum file size: 10MB
                            </small>
                        </div>

                        <!-- Info Alert -->
                        <div class="alert alert-info">
                            <h6 class="alert-heading"><i class="fas fa-lightbulb"></i> File Format Tips:</h6>
                            <ul class="mb-0 pl-3">
                                <li>Excel files (.xlsx, .xls) will be automatically converted to CSV</li>
                                <li>First row should contain column headers</li>
                                <li>Required fields: <strong>Inspection ID</strong> and <strong>Establishment Name</strong></li>
                                <li>Date format should be: dd/mm/yyyy</li>
                            </ul>
                        </div>

                        <!-- Progress Bar (hidden initially) -->
                        <div id="uploadProgress" style="display: none;">
                            <div class="progress mb-2">
                                <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" 
                                    role="progressbar" 
                                    style="width: 0%"
                                    id="uploadProgressBar">
                                    0%
                                </div>
                            </div>
                            <small class="text-muted" id="uploadStatus">Preparing upload...</small>
                        </div>

                        <!-- Results (hidden initially) -->
                        <div id="uploadResults" style="display: none;">
                            <div class="alert alert-success">
                                <h6><i class="fas fa-check-circle"></i> Upload Complete!</h6>
                                <p class="mb-1">
                                    <strong>Records imported:</strong> <span id="successCount">0</span>
                                </p>
                                <div id="errorsList" style="display: none;">
                                    <hr>
                                    <p class="mb-1"><strong>Errors:</strong></p>
                                    <ul id="errorsListContent" class="mb-0 pl-3" style="max-height: 200px; overflow-y: auto;">
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Error Message -->
                        <div id="uploadError" class="alert alert-danger" style="display: none;">
                            <h6><i class="fas fa-exclamation-triangle"></i> Upload Failed</h6>
                            <p class="mb-0"><strong>Error:</strong> <span id="errorMessage"></span></p>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-success" id="uploadBtn">
                            <i class="fas fa-upload"></i> Upload
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Stage Progression Modal -->
    <div class="modal fade" id="stageProgressionModal" tabindex="-1" role="dialog" aria-labelledby="stageProgressionModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title" id="stageProgressionModalLabel">Move to Next Stage</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p id="stageProgressionMessage"></p>
                    <div class="card bg-light">
                        <div class="card-body">
                            <small class="text-muted">
                                <strong>Case:</strong> <span id="stageCaseInfo"></span><br>
                                <strong>Current Stage:</strong> <span id="stageCurrentStage"></span><br>
                                <strong>Next Stage:</strong> <span id="stageNextStage"></span>
                            </small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="confirmStageBtn">
                        <i class="fas fa-arrow-right mr-2"></i>Proceed
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Document History Modal -->
    <div class="modal fade" id="caseHistoryModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                    <h5 class="modal-title">
                        <i class="fas fa-history"></i> Document Transfer History
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" style="color: white;">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <strong>Case:</strong> <span id="historyCaseNo"></span><br>
                        <strong>Establishment:</strong> <span id="historyEstablishment"></span>
                    </div>
                    <hr>
                    <div id="historyContent">
                        <div class="text-center py-4">
                            <div class="spinner-border text-primary" role="status">
                                <span class="sr-only">Loading...</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

@endsection
@push('scripts')
<!-- DataTables plugins -->
<script src="{{ asset('vendor/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('vendor/datatables/dataTables.bootstrap4.min.js') }}"></script>

<script>

$(document).ready(function() {
    console.log('jQuery version:', $.fn.jquery);
    console.log('DataTables available:', typeof $.fn.DataTable !== 'undefined');

    // Store all table instances
    var tables = {};
    
    // Track which tabs have been loaded
    var loadedTabs = {
        'tab0': true,
        'tab1': false,
        'tab2': false,
        'tab3': false,
        'tab4': false,
        'tab5': false,
        'tab6': false,
        'tab7': false
    };

    // DataTable configuration
    var dtConfig = {
        pageLength: 10,
        lengthChange: false,
        paging: true,
        searching: true,
        info: true,
        dom: 'tip',
        order: [[0, "asc"]],
        scrollX: true,
        scrollY: '400px',
        scrollCollapse: true,
        drawCallback: function() {
            $('.sticky-table thead th').css({
                'position': 'sticky',
                'top': 0,
                'z-index': 12
            });
            $('.sticky-table thead th:nth-child(-n+5)').css({
                'z-index': 13
            });
        }
    };

    // Function to safely initialize a DataTable
    function initDataTable(tableId) {
        try {
            if ($(tableId).length === 0) {
                console.warn('Table not found:', tableId);
                return false;
            }
            
            const $tbody = $(tableId + ' tbody');
            const $rows = $tbody.find('tr');
            
            if ($rows.length === 0) {
                console.log('Table has no rows:', tableId);
                return false;
            }
            
            if ($rows.length === 1 && $rows.first().find('td[colspan]').length > 0) {
                console.log('Table has no data (only "no records" message):', tableId);
                return false;
            }
            
            if ($.fn.DataTable.isDataTable(tableId)) {
                $(tableId).DataTable().destroy();
            }
            
            $(tableId).off();
            
            tables[tableId] = $(tableId).DataTable(dtConfig);
            console.log('✓ Initialized ' + tableId);
            
            // IMPORTANT: Bind search after table initialization
            bindSearchForTable(tableId);
            
            return true;
        } catch (error) {
            console.error('✗ Failed to initialize ' + tableId + ':', error);
            return false;
        }
    }

    // NEW: Function to bind search to a specific table
    function bindSearchForTable(tableId) {
        const tabNumber = tableId.replace('#dataTable', '');
        const searchId = '#customSearch' + tabNumber;
        
        // Remove any existing event handlers to prevent duplicates
        $(searchId).off('keyup input change');
        
        // Bind the search
        $(searchId).on('keyup input change', function() {
            if (tables[tableId]) {
                tables[tableId].search(this.value).draw();
                console.log('Search triggered for ' + tableId + ' with value: ' + this.value);
            }
        });
        
        console.log('✓ Search bound for ' + tableId);
    }

    // Function to load tab data via AJAX
    function loadTabData(tabId, tabNumber) {
        const $tabPane = $('#' + tabId);
        const $cardBody = $tabPane.find('.card-body');
        
        $cardBody.html(`
            <div class="tab-loading">
                <div class="spinner-border text-primary" role="status">
                    <span class="sr-only">Loading...</span>
                </div>
                <p class="mt-2">Loading ${tabId.replace('tab', 'stage ')} data...</p>
            </div>
        `);

        $.ajax({
            url: '/case/load-tab/' + tabNumber,
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Accept': 'application/json'
            },
            success: function(response) {
                if (response.success) {
                    $cardBody.html(response.html);
                    loadedTabs[tabId] = true;
                    
                    const tableId = '#dataTable' + tabNumber;
                    setTimeout(function() {
                        initDataTable(tableId);
                    }, 100);
                    
                    console.log(`✓ Loaded ${tabId} with ${response.count} records`);
                } else {
                    $cardBody.html(`
                        <div class="alert alert-danger">
                            Failed to load data. Please try again.
                        </div>
                    `);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading tab data:', error);
                $cardBody.html(`
                    <div class="alert alert-danger">
                        <strong>Error:</strong> Failed to load data. ${xhr.responseJSON?.error || 'Please refresh the page and try again.'}
                    </div>
                `);
            }
        });
    }

    // Update file input label with selected filename
    $('#csv_file').on('change', function() {
        const fileName = $(this).val().split('\\').pop();
        $(this).next('.custom-file-label').html(fileName);
    });

    // Handle CSV/Excel Upload Form Submission
    $('#csvUploadForm').on('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const uploadBtn = $('#uploadBtn');
        const originalBtnText = uploadBtn.html();
        
        // Validate file
        const fileInput = $('#csv_file')[0];
        if (!fileInput.files.length) {
            $('#uploadError').show();
            $('#errorMessage').text('Please select a file');
            return;
        }
        
        const file = fileInput.files[0];
        const fileName = file.name.toLowerCase();
        const validExtensions = ['.csv', '.xlsx', '.xls'];
        const isValidFile = validExtensions.some(ext => fileName.endsWith(ext));
        
        if (!isValidFile) {
            $('#uploadError').show();
            $('#errorMessage').text('Please select a valid CSV or Excel file (.csv, .xlsx, .xls)');
            return;
        }
        
        // Check file size (10MB max)
        const maxSize = 10 * 1024 * 1024; // 10MB in bytes
        if (file.size > maxSize) {
            $('#uploadError').show();
            $('#errorMessage').text('File too large. Maximum size is 10MB.');
            return;
        }
        
        // Hide error, reset and show progress
        $('#uploadError').hide();
        $('#uploadResults').hide();
        $('#errorsList').hide();
        $('#uploadProgress').show();
        $('#uploadProgressBar').css('width', '0%').text('0%');
        $('#uploadStatus').text('Uploading file...');
        
        // Disable upload button
        uploadBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Uploading...');
        
        $.ajax({
            url: '/case/import-csv',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            xhr: function() {
                const xhr = new window.XMLHttpRequest();
                xhr.upload.addEventListener("progress", function(evt) {
                    if (evt.lengthComputable) {
                        const percentComplete = Math.round((evt.loaded / evt.total) * 100);
                        $('#uploadProgressBar').css('width', percentComplete + '%').text(percentComplete + '%');
                        
                        // Update status based on progress
                        if (percentComplete < 100) {
                            $('#uploadStatus').text('Uploading: ' + percentComplete + '%');
                        } else {
                            $('#uploadStatus').text('Processing file...');
                        }
                    }
                }, false);
                return xhr;
            },
            success: function(response) {
                console.log('Upload response:', response);
                
                $('#uploadProgressBar').css('width', '100%').text('100%');
                $('#uploadStatus').text('Processing complete!');
                
                // Show results
                setTimeout(function() {
                    $('#uploadProgress').hide();
                    $('#uploadResults').show();
                    
                    $('#successCount').text(response.success_count || 0);
                    
                    // Show errors if any
                    if (response.errors && response.errors.length > 0) {
                        $('#errorsList').show();
                        let errorHtml = '';
                        response.errors.forEach(function(error) {
                            errorHtml += '<li class="text-danger">' + error + '</li>';
                        });
                        $('#errorsListContent').html(errorHtml);
                    }
                    
                    // Reset button
                    uploadBtn.prop('disabled', false).html(originalBtnText);
                    
                    // Show success alert
                    showAlert('success', response.message || 'File uploaded successfully!');
                    
                    // Reload page after 3 seconds
                    setTimeout(function() {
                        location.reload();
                    }, 3000);
                }, 500);
            },
            error: function(xhr) {
                console.error('Upload error:', xhr);
                
                $('#uploadProgress').hide();
                uploadBtn.prop('disabled', false).html(originalBtnText);
                
                let errorMessage = 'Failed to upload file.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                } else if (xhr.status === 413) {
                    errorMessage = 'File too large. Maximum size is 10MB.';
                } else if (xhr.status === 422) {
                    errorMessage = 'Validation error. Please check your file format.';
                } else if (xhr.status === 500) {
                    errorMessage = 'Server error. Please check the file format and try again.';
                }
                
                $('#uploadError').show();
                $('#errorMessage').text(errorMessage);
                
                showAlert('error', errorMessage);
            }
        });
    });

    // Reset modal when closed
    $('#uploadCsvModal').on('hidden.bs.modal', function() {
        $('#csvUploadForm')[0].reset();
        $('#csv_file').next('.custom-file-label').html('Choose file...');
        $('#uploadProgress').hide();
        $('#uploadResults').hide();
        $('#uploadError').hide();
        $('#errorsList').hide();
        $('#uploadBtn').prop('disabled', false).html('<i class="fas fa-upload"></i> Upload');
    });

    // Auto-minimize sidebar on page load
    $('body').addClass('sidebar-toggled');
    $('.sidebar').addClass('toggled');
    localStorage.setItem('sidebarToggled', 'true');
    
    // Initialize only Tab 0 on page load
    initDataTable('#dataTable0');
    
    // Adjust table after auto-minimize
    setTimeout(function() {
        if (tables['#dataTable0']) {
            tables['#dataTable0'].columns.adjust().draw(false);
        }
    }, 100);

    // Tab switching with lazy loading
    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        var target = $(e.target).attr("href");
        var tableId = target.replace('#tab', '#dataTable');
        var tabId = target.replace('#', '');
        var tabNumber = tabId.replace('tab', '');
        
        console.log('Tab switched to:', target);
        
        if (tabId === 'tab0') {
            if (tables[tableId]) {
                tables[tableId].columns.adjust().draw(false);
            }
            return;
        }
        
        if (!loadedTabs[tabId]) {
            console.log('Loading data for:', tabId);
            loadTabData(tabId, tabNumber);
        } else {
            console.log('Adjusting columns for:', tableId);
            if (tables[tableId]) {
                tables[tableId].columns.adjust().draw(false);
            }
        }
        
        setTimeout(function() {
            if (tables[tableId]) {
                tables[tableId].columns.adjust().draw(false);
            }
        }, 100);
    });

    // Fix sidebar toggle with state persistence
    $('#sidebarToggle, #sidebarToggleTop').on('click', function() {
        var isToggled = $('body').hasClass('sidebar-toggled');
        localStorage.setItem('sidebarToggled', !isToggled);
        
        setTimeout(function() {
            var activeTab = $('.tab-pane.active').attr('id');
            var tableId = '#dataTable' + activeTab.replace('tab', '');
            if (tables[tableId]) {
                tables[tableId].columns.adjust().draw(false);
            }
        }, 350);
    });

    // REMOVED: Individual search bindings (now handled by bindSearchForTable)
    // The old code was trying to bind to tables that don't exist yet

    // Handle active tab from session
    @if(session('active_tab'))
        const activeTab = '{{ session("active_tab") }}';
        console.log('Activating tab from session:', activeTab);
        setTimeout(function() {
            $('a[href="#' + activeTab + '"]').tab('show');
        }, 100);
    @endif

    console.log('Initialization complete');
    // ... rest of your existing code (delete handlers, inline editing, etc.) ...
    // Keep all your existing modal, delete, and inline editing code below this line
    
    // Modal handling for Add/Edit Cases
    $('#addCaseModal').on('show.bs.modal', function(event) {
        var button = $(event.relatedTarget);
        var mode = button.data('mode') || 'add';
        var modal = $(this);
        
        modal.find('#addCaseModalLabel').text(mode === 'add' ? 'Add New Case' : 'Edit Case');

        if (mode === 'edit') {
            var caseId = button.data('case-id');
            modal.find('#caseForm')[0].reset();
        } else {
            modal.find('#caseForm')[0].reset();
        }
    });

    let caseToDelete = null;

    // Universal delete handler for all record types
    $(document).on('click', '.delete-btn', function(e) {
        e.preventDefault();
        const button = $(this);
        const row = button.closest('tr');
        
        // Check all record types
        const inspectionId = button.data('inspection-id');
        const caseId = button.data('case-id');
        const docketingId = button.data('docketing-id');
        const hearingId = button.data('hearing-id');
        const reviewId = button.data('review-id');
        const orderId = button.data('order-id');
        const complianceId = button.data('compliance-id');
        const appealId = button.data('appeal-id');
        
        console.log('Delete button clicked - inspectionId:', inspectionId, 'caseId:', caseId, 'docketingId:', docketingId, 'hearingId:', hearingId, 'reviewId:', reviewId, 'orderId:', orderId, 'complianceId:', complianceId, 'appealId:', appealId);
        
        if (!inspectionId && !caseId && !docketingId && !hearingId && !reviewId && !orderId && !complianceId && !appealId) {
            console.error('No ID found on delete button');
            showAlert('error', 'Error: Could not identify record');
            return;
        }
        
        const recordId = inspectionId || caseId || docketingId || hearingId || reviewId || orderId || complianceId || appealId;
        const recordType = inspectionId ? 'inspection' : (caseId ? 'case' : (docketingId ? 'docketing' : (hearingId ? 'hearing' : (reviewId ? 'review' : (orderId ? 'order' : (complianceId ? 'compliance' : 'appeal'))))));
        
        const establishment = button.data('establishment') || 'N/A';
        const inspector = button.data('inspector') || 'N/A';
        
        caseToDelete = {
            id: recordId,
            type: recordType,
            row: row,
            button: button
        };
        
        console.log('caseToDelete object:', caseToDelete);
        
        // Build the info display
        let infoHtml = `<strong>Establishment:</strong> ${establishment}<br>`;
        if (inspector && inspector !== 'N/A') {
            infoHtml += `<strong>Inspector:</strong> ${inspector}`;
        }
        
        $('#deleteCaseInfo').html(infoHtml);
        $('#deleteCaseModal').modal('show');
    });

    // Confirm delete button
    $(document).off('click', '#confirmDeleteBtn').on('click', '#confirmDeleteBtn', function() {
        if (!caseToDelete) {
            console.error('caseToDelete is null');
            return;
        }
        
        const csrfToken = $('meta[name="csrf-token"]').attr('content');
        let url;
        
        if (caseToDelete.type === 'inspection') {
            url = `/inspection/${caseToDelete.id}`;
        } else {
            url = `/case/${caseToDelete.id}`;
        }
        
        console.log('Deleting:', caseToDelete.type, 'at URL:', url);
        
        caseToDelete.button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
        
        $.ajax({
            url: url,
            type: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            success: function(response) {
                console.log('Delete successful:', response);
                $('#deleteCaseModal').modal('hide');
                
                caseToDelete.row.fadeOut(300, function() {
                    $(this).remove();
                    
                    // Check if table is now empty
                    const table = caseToDelete.row.closest('table');
                    const tbody = table.find('tbody');
                    if (tbody.find('tr:visible').length === 0) {
                        const colspan = table.find('thead th').length;
                        tbody.html(`<tr><td colspan="${colspan}" class="text-center">No records found.</td></tr>`);
                    }
                });
                
                showAlert('success', response.message || 'Record deleted successfully!');
                caseToDelete = null;
            },
            error: function(xhr, status, error) {
                console.error('Delete error:', error, xhr);
                caseToDelete.button.prop('disabled', false).html('<i class="fas fa-trash"></i>');
                
                let errorMessage = 'Failed to delete record.';
                if (xhr.responseJSON?.message) {
                    errorMessage = xhr.responseJSON.message;
                } else if (xhr.status === 404) {
                    errorMessage = 'Record not found.';
                } else if (xhr.status === 500) {
                    errorMessage = 'Server error occurred.';
                }
                
                showAlert('error', errorMessage);
                $('#deleteCaseModal').modal('hide');
                caseToDelete = null;
            }
        });
    });

let caseToProgress = null;

// Stage progression handler
$(document).on('click', '.move-to-next-stage-btn', function(e) {
    e.preventDefault();
    const button = $(this);
    const caseId = button.data('case-id');
    
    console.log('Next Stage button clicked - caseId:', caseId);
    
    if (!caseId) {
        console.error('No case ID found on button');
        showAlert('error', 'Error: Could not identify case');
        return;
    }
    
    const caseNo = button.data('case-no') || 'N/A';
    const establishment = button.data('establishment') || 'N/A';
    const currentStage = button.data('stage') || 'Unknown';
    
    const stageMap = {
        'Inspections': 'Docketing',
        'Docketing': 'Hearing Process',
        'Hearing Process': 'Review & Drafting',
        'Review & Drafting': 'Orders & Disposition',
        'Orders & Disposition': 'Compliance & Awards',
        'Compliance & Awards': 'Appeals & Resolution',
        'Appeals & Resolution': 'Complete (Archive)'
    };
    
    const nextStage = stageMap[currentStage] || 'Next Stage';
    const isFinalStage = currentStage === 'Appeals & Resolution';
    
    caseToProgress = {
        id: caseId,
        button: button
    };
    
    console.log('caseToProgress object:', caseToProgress);
    
    const message = isFinalStage 
        ? `<strong>Complete ${currentStage} and move case to archived?</strong><br><small>This will mark the case as completed.</small>`
        : `<strong>Complete ${currentStage} and move to ${nextStage}?</strong>`;
    
    $('#stageProgressionMessage').html(message);
    $('#stageCaseInfo').text(`${caseNo} - ${establishment}`);
    $('#stageCurrentStage').text(currentStage);
    $('#stageNextStage').text(nextStage);
    
    console.log('Showing stage progression modal');
    $('#stageProgressionModal').modal('show');
});

// Confirm stage progression
$(document).off('click', '#confirmStageBtn').on('click', '#confirmStageBtn', function() {
    if (!caseToProgress) {
        console.error('caseToProgress is null');
        return;
    }
    
    const csrfToken = $('meta[name="csrf-token"]').attr('content');
    const url = `/case/${caseToProgress.id}/next-stage`;
    
    console.log('Moving case to next stage at URL:', url);
    
    const button = caseToProgress.button;
    button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
    
    $.ajax({
        url: url,
        type: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        success: function(response) {
            console.log('Stage progression successful:', response);
            $('#stageProgressionModal').modal('hide');
            
            showAlert('success', response.message || 'Case moved to next stage successfully!');
            
            // Reload page after 1.5 seconds to refresh the data
            setTimeout(() => {
                location.reload();
            }, 1500);
            
            caseToProgress = null;
        },
        error: function(xhr, status, error) {
            console.error('Stage progression error:', error, xhr);
            button.prop('disabled', false).html('<i class="fas fa-arrow-right"></i> Next');
            
            let errorMessage = 'Failed to move case to next stage.';
            if (xhr.responseJSON?.message) {
                errorMessage = xhr.responseJSON.message;
            } else if (xhr.status === 404) {
                errorMessage = 'Case not found.';
            } else if (xhr.status === 500) {
                errorMessage = 'Server error occurred.';
            }
            
            showAlert('error', errorMessage);
            $('#stageProgressionModal').modal('hide');
            caseToProgress = null;
        }
    });
});

// Fix aria-hidden warnings on modals by managing focus properly
$(document).on('hide.bs.modal', '#deleteCaseModal, #stageProgressionModal', function() {
    // Move focus to body before modal hides to prevent focus trap
    setTimeout(() => {
        $('body').focus();
    }, 0);
});

$(document).on('shown.bs.modal', '#deleteCaseModal, #stageProgressionModal', function() {
    // Focus the first focusable element in the modal
    const firstFocusable = $(this).find('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])').first();
    if (firstFocusable.length) {
        firstFocusable.focus();
    }
});

    // Handle edit button click for Cases
    $(document).on('click', '.btn-warning[data-target="#addCaseModal"]', function() {
        var caseId = $(this).data('case-id');
        if (caseId) {
            $.get('/case/' + caseId + '/edit', function(data) {
                $('#inspection_id').val(data.inspection_id);
                $('#case_no').val(data.case_no);
                $('#establishment_name').val(data.establishment_name);
                $('#current_stage').val(data.current_stage);
                $('#overall_status').val(data.overall_status);
                
                $('#caseForm').attr('action', '/case/' + caseId);
                $('#formMethod').val('PUT');
            });
        }
    });

    // Auto-dismiss alerts after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);

});

// Function to show alert messages
function showAlert(type, message) {
    console.log('Showing alert:', type, message);
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const alertHtml = `
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    `;
    
    $('.tab-pane.active .card-body').prepend(alertHtml);
    
    setTimeout(function() {
        $('.alert').fadeOut();
    }, 2000);
}

// Unified inline editing system (keep all your existing inline editing code)
$(document).ready(function() {
    let currentEditingRow = null;
    let originalData = {};
    let currentTab = null;

    // Tab configuration
    const tabConfigs = {
        'tab0': {
            name: 'case',
            endpoint: '/case/',
            editBtnClass: '.edit-row-btn-case',
            saveBtnClass: '.save-btn-case', 
            cancelBtnClass: '.cancel-btn-case',
            alertPrefix: 'tab0',
            fields: {
                // Core Information
                'no': { type: 'text' },
                'inspection_id': { type: 'text' },
                'case_no': { type: 'text' },
                'establishment_name': { type: 'text' },
                'establishment_address': { type: 'text' }, 
                'mode': { type: 'text' },                  
                'po_office': { type: 'text' },
                'current_stage': { 
                    type: 'select',
                    options: [
                        { value: '', text: 'Select Stage' },
                        { value: '1: Inspections', text: 'Inspections' },
                        { value: '2: Docketing', text: 'Docketing' },
                        { value: '3: Hearing', text: 'Hearing' },
                        { value: '4: Review & Drafting', text: 'Review & Drafting' },
                        { value: '5: Orders & Disposition', text: 'Orders & Disposition' },
                        { value: '6: Compliance & Awards', text: 'Compliance & Awards' },
                        { value: '7: Appeals & Resolution', text: 'Appeals & Resolution' }
                    ]
                },
                'overall_status': {
                    type: 'select',
                    options: [
                        { value: '', text: 'Select Status' },
                        { value: 'Active', text: 'Active' },
                        { value: 'Completed', text: 'Completed' },
                        { value: 'Dismissed', text: 'Dismissed' }
                    ]
                },
                
                // Inspection Stage
                'date_of_inspection': { type: 'date' },
                'inspector_name': { type: 'text' },
                'inspector_authority_no': { type: 'text' },
                'date_of_nr': { type: 'date' },
                'lapse_20_day_period': { type: 'text' },
                
                // Docketing Stage
                'pct_for_docketing': { type: 'text' },
                'date_scheduled_docketed': { type: 'date' },
                'aging_docket': { type: 'number' },
                'status_docket': {
                    type: 'select',
                    options: [
                        { value: '', text: 'Select Status' },
                        { value: 'Pending', text: 'Pending' },
                        { value: 'Completed', text: 'Completed' },
                        { value: 'In Progress', text: 'In Progress' },
                        { value: 'Cancelled', text: 'Cancelled' }
                    ]
                },
                'hearing_officer_mis': { type: 'text' },
                
                // Hearing Process Stage
                'date_1st_mc_actual': { type: 'date' },
                'first_mc_pct': { type: 'text' },
                'status_1st_mc': {
                    type: 'select',
                    options: [
                        { value: '', text: 'Select Status' },
                        { value: 'Pending', text: 'Pending' },
                        { value: 'Ongoing', text: 'Ongoing' },
                        { value: 'Completed', text: 'Completed' }
                    ]
                },
                'date_2nd_last_mc': { type: 'date' },
                'second_last_mc_pct': { type: 'text' },
                'status_2nd_mc': {
                    type: 'select',
                    options: [
                        { value: '', text: 'Select Status' },
                        { value: 'Pending', text: 'Pending' },
                        { value: 'In Progress', text: 'In Progress' },
                        { value: 'Completed', text: 'Completed' }
                    ]
                },
                'case_folder_forwarded_to_ro': { type: 'text' },
                'draft_order_from_po_type': { type: 'text' },
                'applicable_draft_order': {
                    type: 'select',
                    options: [
                        { value: '', text: 'Select' },
                        { value: 'Y', text: 'Yes' },
                        { value: 'N', text: 'No' }
                    ]
                },
                'complete_case_folder': {
                    type: 'select',
                    options: [
                        { value: '', text: 'Select' },
                        { value: 'Y', text: 'Yes' },
                        { value: 'N', text: 'No' }
                    ]
                },
                'twg_ali': { type: 'text' },
                
                // Review & Drafting Stage
                'po_pct': { type: 'text' },
                'aging_po_pct': { type: 'number' },
                'status_po_pct': {
                    type: 'select',
                    options: [
                        { value: '', text: 'Select Status' },
                        { value: 'Pending', text: 'Pending' },
                        { value: 'Ongoing', text: 'Ongoing' },
                        { value: 'Overdue', text: 'Overdue' },
                        { value: 'Completed', text: 'Completed' }
                    ]
                },
                'date_received_from_po': { type: 'date' },
                'reviewer_drafter': { type: 'text' },
                'date_received_by_reviewer': { type: 'date' },
                'date_returned_from_drafter': { type: 'date' },
                'aging_10_days_tssd': { type: 'number' },
                'status_reviewer_drafter': {
                    type: 'select',
                    options: [
                        { value: '', text: 'Select Status' },
                        { value: 'Pending', text: 'Pending' },
                        { value: 'Ongoing', text: 'Ongoing' },
                        { value: 'Returned', text: 'Returned' },
                        { value: 'Approved', text: 'Approved' },
                        { value: 'Overdue', text: 'Overdue' }
                    ]
                },
                'draft_order_tssd_reviewer': { type: 'text' },
                'final_review_date_received': { type: 'date' },
                'date_received_drafter_finalization': { type: 'date' },
                'date_returned_case_mgmt_signature': { type: 'date' },
                'aging_2_days_finalization': { type: 'number' },
                'status_finalization': {
                    type: 'select',
                    options: [
                        { value: '', text: 'Select Status' },
                        { value: 'Pending', text: 'Pending' },
                        { value: 'In Progress', text: 'In Progress' },
                        { value: 'Completed', text: 'Completed' },
                        { value: 'Overdue', text: 'Overdue' }
                    ]
                },
                
                // Orders & Disposition Stage
                'pct_96_days': { type: 'text' },
                'date_signed_mis': { type: 'date' },
                'status_pct': {
                    type: 'select',
                    options: [
                        { value: '', text: 'Select Status' },
                        { value: 'Pending', text: 'Pending' },
                        { value: 'Ongoing', text: 'Ongoing' },
                        { value: 'Completed', text: 'Completed' },
                        { value: 'Overdue', text: 'Overdue' }
                    ]
                },
                'reference_date_pct': { type: 'date' },
                'aging_pct': { type: 'number' },
                'disposition_mis': { type: 'text' },
                'disposition_actual': { type: 'text' },
                'findings_to_comply': { type: 'text' },
                'compliance_order_monetary_award': { type: 'number', step: '0.01' },
                'osh_penalty': { type: 'number', step: '0.01' },
                'affected_male': { type: 'number' },
                'affected_female': { type: 'number' },
                'date_of_order_actual': { type: 'date' },
                'released_date_actual': { type: 'date' },
                
                // Compliance & Awards Stage
                'first_order_dismissal_cnpc': {
                    type: 'select',
                    options: [
                        { value: '', text: 'Select' },
                        { value: '0', text: 'No' },
                        { value: '1', text: 'Yes' }
                    ]
                },
                'tavable_less_than_10_workers': {
                    type: 'select',
                    options: [
                        { value: '', text: 'Select' },
                        { value: '0', text: 'No' },
                        { value: '1', text: 'Yes' }
                    ]
                },
                'scanned_order_first': { type: 'text' },
                'with_deposited_monetary_claims': {
                    type: 'select',
                    options: [
                        { value: '', text: 'Select' },
                        { value: '0', text: 'No' },
                        { value: '1', text: 'Yes' }
                    ]
                },
                'amount_deposited': { type: 'number', step: '0.01' },
                'with_order_payment_notice': {
                    type: 'select',
                    options: [
                        { value: '', text: 'Select' },
                        { value: '0', text: 'No' },
                        { value: '1', text: 'Yes' }
                    ]
                },
                'status_all_employees_received': {
                    type: 'select',
                    options: [
                        { value: '', text: 'Select Status' },
                        { value: 'Pending', text: 'Pending' },
                        { value: 'Yes', text: 'Yes' },
                        { value: 'No', text: 'No' },
                        { value: 'Partial', text: 'Partial' }
                    ]
                },
                'status_case_after_first_order': { type: 'text' },
                'date_notice_finality_dismissed': { type: 'date' },
                'released_date_notice_finality': { type: 'date' },
                'scanned_notice_finality': { type: 'text' },
                'updated_ticked_in_mis': {
                    type: 'select',
                    options: [
                        { value: '', text: 'Select' },
                        { value: '0', text: 'No' },
                        { value: '1', text: 'Yes' }
                    ]
                },
                
                // Appeals & Resolution Stage (2nd Order)
                'second_order_drafter': { type: 'text' },
                'date_received_by_drafter_ct_cnpc': { type: 'date' },
                'date_returned_case_mgmt_ct_cnpc': { type: 'date' },
                'review_ct_cnpc': { type: 'text' },
                'date_received_drafter_finalization_2nd': { type: 'date' },
                'date_returned_case_mgmt_signature_2nd': { type: 'date' },
                'date_order_2nd_cnpc': { type: 'date' },
                'released_date_2nd_cnpc': { type: 'date' },
                'scanned_order_2nd_cnpc': { type: 'text' },
                
                // Appeals & Resolution Stage (MALSU)
                'date_forwarded_malsu': { type: 'date' },
                'scanned_indorsement_malsu': { type: 'text' },
                'motion_reconsideration_date': { type: 'date' },
                'date_received_malsu': { type: 'date' },
                'date_resolution_mr': { type: 'date' },
                'released_date_resolution_mr': { type: 'date' },
                'scanned_resolution_mr': { type: 'text' },
                'date_appeal_received_records': { type: 'date' },
                'date_indorsed_office_secretary': { type: 'date' },
                
                // Additional Information
                'logbook_page_number': { type: 'text' },
                'remarks_notes': { type: 'text' }
            }
        },
        'tab1': {
            name: 'inspection',
            endpoint: '/inspection/',
            editBtnClass: '.edit-row-btn',
            saveBtnClass: '.save-btn',
            cancelBtnClass: '.cancel-btn',
            alertPrefix: 'tab1',
            fields: {
                'inspection_id': { type: 'text' },
                'establishment_name': { type: 'text' },
                'po_office': { type: 'text' },
                'inspector_name': { type: 'text' },
                'inspector_authority_no': { type: 'text' },
                'date_of_inspection': { type: 'date' },
                'date_of_nr': { type: 'date' },
                'twg_ali': { type: 'text' }
            }
        },
        'tab2': {
            name: 'docketing',
            endpoint: '/docketing/',
            editBtnClass: '.edit-row-btn-docketing',
            saveBtnClass: '.save-btn-docketing',
            cancelBtnClass: '.cancel-btn-docketing',
            alertPrefix: 'tab2',
            fields: {
                'pct_for_docketing': { type: 'text' },
                'date_scheduled_docketed': { type: 'date' },
                'aging_docket': { type: 'text' },
                'status_docket': {
                    type: 'select',
                    options: [
                        { value: '', text: 'Select Status' },
                        { value: 'Pending', text: 'Pending' },
                        { value: 'Completed', text: 'Completed' },
                        { value: 'In Progress', text: 'In Progress' },
                        { value: 'Cancelled', text: 'Cancelled' }
                    ]
                },
                'hearing_officer_mis': { type: 'text' }
            }
        },
        'tab3': {
            name: 'hearing',
            endpoint: '/hearing-process/',
            editBtnClass: '.edit-row-btn-hearing',
            saveBtnClass: '.save-btn-hearing',
            cancelBtnClass: '.cancel-btn-hearing',
            alertPrefix: 'tab3',
            fields: {
                'date_1st_mc_actual': { type: 'date' },
                'first_mc_pct': { type: 'text' },
                'status_1st_mc': {
                    type: 'select',
                    options: [
                        { value: '', text: 'Select Status' },
                        { value: 'Pending', text: 'Pending' },
                        { value: 'Ongoing', text: 'Ongoing' },
                        { value: 'Completed', text: 'Completed' }
                    ]
                },
                'date_2nd_last_mc': { type: 'date' },
                'second_last_mc_pct': { type: 'text' },
                'status_2nd_mc': {
                    type: 'select',
                    options: [
                        { value: '', text: 'Select Status' },
                        { value: 'Pending', text: 'Pending' },
                        { value: 'In Progress', text: 'In Progress' },
                        { value: 'Completed', text: 'Completed' }
                    ]
                },
                'case_folder_forwarded_to_ro': { type: 'text' },
                'complete_case_folder': {
                    type: 'select',
                    options: [
                        { value: 'N', text: 'No' },
                        { value: 'Y', text: 'Yes' }
                    ]
                }
            }
        },
        'tab4': {
            name: 'review-and-drafting',
            endpoint: '/review-and-drafting/',
            editBtnClass: '.edit-row-btn-review',
            saveBtnClass: '.save-btn-review',
            cancelBtnClass: '.cancel-btn-review',
            alertPrefix: 'tab4',
            fields: {
                'draft_order_type': { type: 'text' },
                'applicable_draft_order': {
                    type: 'select',
                    options: [
                        { value: 'Y', text: 'Yes' },
                        { value: 'N', text: 'No' }
                    ]
                },
                'po_pct': { type: 'number' },
                'aging_po_pct': { type: 'number' },
                'status_po_pct': {
                    type: 'select',
                    options: [
                        { value: '', text: 'Select Status' },
                        { value: 'Pending', text: 'Pending' },
                        { value: 'Ongoing', text: 'Ongoing' },
                        { value: 'Overdue', text: 'Overdue' },
                        { value: 'Completed', text: 'Completed' }
                    ]
                },
                'date_received_from_po': { type: 'date' },
                'reviewer_drafter': { type: 'text' },
                'date_received_by_reviewer': { type: 'date' },
                'date_returned_from_drafter': { type: 'date' },
                'aging_10_days_tssd': { type: 'number' },
                'status_reviewer_drafter': {
                    type: 'select',
                    options: [
                        { value: '', text: 'Select Status' },
                        { value: 'Pending', text: 'Pending' },
                        { value: 'Ongoing', text: 'Ongoing' },
                        { value: 'Returned', text: 'Returned' },
                        { value: 'Approved', text: 'Approved' },
                        { value: 'Overdue', text: 'Overdue' }
                    ]
                },
                'draft_order_tssd_reviewer': { type: 'text' }
            }
        },
        'tab5': {
            name: 'orders-and-disposition',
            endpoint: '/orders-and-disposition/',
            editBtnClass: '.edit-row-btn-orders',
            saveBtnClass: '.save-btn-orders',
            cancelBtnClass: '.cancel-btn-orders',
            alertPrefix: 'tab5',
            fields: {
                'aging_2_days_finalization': { type: 'number' },
                'status_finalization': {
                    type: 'select',
                    options: [
                        { value: '', text: 'Select Status' },
                        { value: 'Pending', text: 'Pending' },
                        { value: 'In Progress', text: 'In Progress' },
                        { value: 'Completed', text: 'Completed' },
                        { value: 'Overdue', text: 'Overdue' }
                    ]
                },
                'pct_96_days': { type: 'number' },
                'date_signed_mis': { type: 'date' },
                'status_pct': {
                    type: 'select',
                    options: [
                        { value: '', text: 'Select Status' },
                        { value: 'Pending', text: 'Pending' },
                        { value: 'Ongoing', text: 'Ongoing' },
                        { value: 'Completed', text: 'Completed' },
                        { value: 'Overdue', text: 'Overdue' }
                    ]
                },
                'reference_date_pct': { type: 'date' },
                'aging_pct': { type: 'number' },
                'disposition_mis': { type: 'text' },
                'disposition_actual': { type: 'text' },
                'findings_to_comply': { type: 'text' },
                'date_of_order_actual': { type: 'date' },
                'released_date_actual': { type: 'date' }
            }
        },
        'tab6': {
            name: 'compliance-and-awards',
            endpoint: '/compliance-and-awards/',
            editBtnClass: '.edit-row-btn-compliance',
            saveBtnClass: '.save-btn-compliance',
            cancelBtnClass: '.cancel-btn-compliance',
            alertPrefix: 'tab6',
            fields: {
                'compliance_order_monetary_award': { type: 'number', step: '0.01' },
                'osh_penalty': { type: 'number', step: '0.01' },
                'affected_male': { type: 'number' },
                'affected_female': { type: 'number' },
                'first_order_dismissal_cnpc': {
                    type: 'select',
                    options: [
                        { value: '0', text: 'No' },
                        { value: '1', text: 'Yes' }
                    ]
                },
                'tavable_less_than_10_workers': {
                    type: 'select',
                    options: [
                        { value: '0', text: 'No' },
                        { value: '1', text: 'Yes' }
                    ]
                },
                'with_deposited_monetary_claims': {
                    type: 'select',
                    options: [
                        { value: '0', text: 'No' },
                        { value: '1', text: 'Yes' }
                    ]
                },
                'amount_deposited': { type: 'number', step: '0.01' },
                'with_order_payment_notice': {
                    type: 'select',
                    options: [
                        { value: '0', text: 'No' },
                        { value: '1', text: 'Yes' }
                    ]
                },
                'status_all_employees_received': {
                    type: 'select',
                    options: [
                        { value: '', text: 'Select Status' },
                        { value: 'Pending', text: 'Pending' },
                        { value: 'Yes', text: 'Yes' },
                        { value: 'No', text: 'No' },
                        { value: 'Partial', text: 'Partial' }
                    ]
                },
                'status_case_after_first_order': { type: 'text' },
                'date_notice_finality_dismissed': { type: 'date' },
                'released_date_notice_finality': { type: 'date' },
                'updated_ticked_in_mis': {
                    type: 'select',
                    options: [
                        { value: '0', text: 'No' },
                        { value: '1', text: 'Yes' }
                    ]
                },
                'second_order_drafter': { type: 'text' },
                'date_received_by_drafter_ct_cnpc': { type: 'date' }
            }
        }, 
        'tab7': {
            name: 'appeals-and-resolution',
            endpoint: '/appeals-and-resolution/',
            editBtnClass: '.edit-row-btn-appeals',
            saveBtnClass: '.save-btn-appeals',
            cancelBtnClass: '.cancel-btn-appeals',
            alertPrefix: 'tab7',
            fields: {
                'date_returned_case_mgmt': { type: 'date' },
                'review_ct_cnpc': { type: 'text' },
                'date_received_drafter_finalization_2nd': { type: 'date' },
                'date_returned_case_mgmt_signature_2nd': { type: 'date' },
                'date_order_2nd_cnpc': { type: 'date' },
                'released_date_2nd_cnpc': { type: 'date' },
                'date_forwarded_malsu': { type: 'date' },
                'motion_reconsideration_date': { type: 'date' },
                'date_received_malsu': { type: 'date' },
                'date_resolution_mr': { type: 'date' },
                'released_date_resolution_mr': { type: 'date' },
                'date_appeal_received_records': { type: 'date' }
            }
        }
    };

    // Get current active tab
    function getCurrentTab() {
        return $('.tab-pane.active').attr('id') || 'tab0';
    }

    // Get tab config
    function getTabConfig(tabId = null) {
        tabId = tabId || getCurrentTab();
        return tabConfigs[tabId];
    }

    // Unified edit button click handler
    $(document).on('click', '.edit-row-btn, .edit-row-btn-case, .edit-row-btn-docketing, .edit-row-btn-hearing, .edit-row-btn-review, .edit-row-btn-orders, .edit-row-btn-compliance, .edit-row-btn-appeals', function() {
        const row = $(this).closest('tr');
        currentTab = getCurrentTab();
        
        if (currentEditingRow && currentEditingRow.get(0) !== row.get(0)) {
            cancelEdit();
        }
        
        enableRowEdit(row);
    });

    // Unified save button click handler  
    $(document).on('click', '.save-btn, .save-btn-case, .save-btn-docketing, .save-btn-hearing, .save-btn-review, .save-btn-orders, .save-btn-compliance, .save-btn-appeals', function() {
        const row = $(this).closest('tr');
        const recordId = row.data('id');
        const config = getTabConfig(currentTab);
        
        if (!recordId) {
            showAlert(`Invalid ${config.name} ID. Please refresh the page.`, 'danger');
            return;
        }
        
        const updatedData = collectRowData(row, config);
        saveData(recordId, updatedData, row, config);
    });

    // Unified cancel button click handler
    $(document).on('click', '.cancel-btn, .cancel-btn-case, .cancel-btn-docketing, .cancel-btn-hearing, .cancel-btn-review, .cancel-btn-orders, .cancel-btn-compliance, .cancel-btn-appeals', function() {
        cancelEdit();
    });

    // ESC key to cancel edit
    $(document).on('keyup', function(e) {
        if (e.key === 'Escape' && currentEditingRow) {
            cancelEdit();
        }
    });

    // Enter key to save
    $(document).on('keyup', '.edit-input', function(e) {
        if (e.key === 'Enter') {
            $(`.save-btn, .save-btn-case, .save-btn-docketing, .save-btn-hearing, .save-btn-review, .save-btn-orders, .save-btn-compliance, .save-btn-appeals`).filter(':visible').click();
        }
    });

    function enableRowEdit(row) {
        currentEditingRow = row;
        const config = getTabConfig(currentTab);
        originalData = {};
        
        row.find('.editable-cell:not(.readonly-cell)').each(function() {
            const cell = $(this);
            const field = cell.data('field');
            originalData[field] = cell.text().trim();
            
            const input = createInput(field, cell, config);
            cell.html(input);
            cell.addClass('edit-mode');
        });
        
        const actionsCell = row.find('td:last');
        const currentButtons = actionsCell.html();
        actionsCell.data('original-buttons', currentButtons);
        
        const buttonSuffix = config.name === 'case' ? '-case' : 
                            config.name === 'docketing' ? '-docketing' :
                            config.name === 'hearing' ? '-hearing' :
                            config.name === 'review-and-drafting' ? '-review' :
                            config.name === 'orders-and-disposition' ? '-orders' :
                            config.name === 'compliance-and-awards' ? '-compliance' :
                            config.name === 'appeals-and-resolution' ? '-appeals' : '';
        
        actionsCell.html(`
            <div class="save-cancel-buttons">
                <button class="btn btn-success btn-sm save-btn${buttonSuffix}" title="Save">
                    <i class="fas fa-check"></i>
                </button>
                <button class="btn btn-secondary btn-sm cancel-btn${buttonSuffix} ml-1" title="Cancel">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `);
        
        row.find('.edit-input').first().focus();
    }

    function createInput(field, cell, config) {
        const fieldConfig = config.fields[field];
        const currentValue = cell.text().trim() === '-' ? '' : cell.text().trim();
        
        if (fieldConfig && fieldConfig.type === 'select') {
            let selectHtml = `<select class="form-control form-control-sm edit-input" data-field="${field}">`;
            
            fieldConfig.options.forEach(option => {
                const isSelected = (currentValue === option.text || currentValue === option.value) ? 'selected' : '';
                selectHtml += `<option value="${option.value}" ${isSelected}>${option.text}</option>`;
            });
            
            selectHtml += '</select>';
            return selectHtml;
        } else if (fieldConfig && fieldConfig.type === 'date') {
            return `<input type="date" class="form-control form-control-sm edit-input" value="${currentValue}" data-field="${field}">`;
        } else if (fieldConfig && fieldConfig.type === 'number') {
            const step = fieldConfig.step || '1';
            return `<input type="number" step="${step}" class="form-control form-control-sm edit-input" value="${currentValue}" data-field="${field}">`;
        } else {
            let inputValue = currentValue;
            if (field === 'establishment_name') {
                inputValue = cell.attr('title') || currentValue;
            }
            return `<input type="text" class="form-control form-control-sm edit-input" value="${inputValue}" data-field="${field}">`;
        }
    }

    function collectRowData(row, config) {
        const updatedData = {};
        
        row.find('.edit-input').each(function() {
            const input = $(this);
            const field = input.data('field');
            updatedData[field] = input.val().trim();
        });
        
        return updatedData;
    }

    function saveData(recordId, data, row, config) {

            console.log('=== SAVE DATA DEBUG ===');
    console.log('Record ID:', recordId);
    console.log('Data being sent:', data);
    console.log('Endpoint:', `${config.endpoint}${recordId}/inline-update`);
    console.log('=======================');

    
        const saveBtn = row.find(`${config.saveBtnClass}`);
        const cancelBtn = row.find(`${config.cancelBtnClass}`);
        const originalSaveContent = saveBtn.html();
        
        saveBtn.html('<i class="fas fa-spinner fa-spin"></i>').prop('disabled', true);
        cancelBtn.prop('disabled', true);
        
        const csrfToken = $('meta[name="csrf-token"]').attr('content');
        
        if (!csrfToken) {
            showAlert('CSRF token not found. Please refresh the page.', 'danger');
            restoreButtons(saveBtn, cancelBtn, originalSaveContent);
            return;
        }

        const cleanedData = {};
        Object.keys(data).forEach(key => {
            const value = data[key];
            cleanedData[key] = (value === '' || value === null || value === undefined) ? null : value.trim();
        });

        $.ajax({
            url: `${config.endpoint}${recordId}/inline-update`,
            method: 'PUT',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            data: cleanedData,
            success: function(response) {
                if (response.success) {
                    updateRowDisplay(row, response.data, config);
                    restoreActionButtons(row);
                    showAlert(response.message || `${config.name} updated successfully!`, 'success');
                    resetEditState();
                } else {
                    throw new Error(response.message || 'Update failed');
                }
            },
            error: function(xhr, status, error) {
                restoreButtons(saveBtn, cancelBtn, originalSaveContent);
                handleAjaxError(xhr, `Error updating ${config.name}!`);
            }
        });
    }

function updateRowDisplay(row, responseData, config) {
    row.find('.editable-cell').each(function() {
        const cell = $(this);
        const field = cell.data('field');
        let displayValue = null;

        // Special handling for docketing table - check both responseData and case relationship
        if (config.name === 'docketing' && responseData.case) {
            // If the field is from the case, get it from there
            if (['inspection_id', 'case_no', 'establishment_name', 'current_stage', 'overall_status'].includes(field)) {
                if (field === 'current_stage') {
                    displayValue = responseData.case[field];
                    if (displayValue && displayValue.includes(': ')) {
                        displayValue = displayValue.split(': ')[1];
                    }
                } else if (field === 'establishment_name') {
                    displayValue = responseData.case[field];
                    cell.attr('title', displayValue);
                    if (displayValue && displayValue.length > 25) {
                        displayValue = displayValue.substring(0, 25) + '...';
                    }
                } else {
                    displayValue = responseData.case[field];
                }
            } else {
                // Otherwise get it from the docketing record
                displayValue = responseData[field];
            }
        } else {
            // For other tables, just use responseData directly
            displayValue = responseData[field];
        }

        if (displayValue === null || displayValue === undefined || displayValue === '') {
            displayValue = '-';
        }
        
        if (field === 'current_stage' && displayValue.includes(': ')) {
            displayValue = displayValue.split(': ')[1];
        }
        
        const statusFields = ['status_docket', 'status_1st_mc', 'status_2nd_mc', 'status_po_pct', 'status_reviewer_drafter', 'status_finalization', 'status_pct', 'status_all_employees_received'];
        if (statusFields.includes(field) && displayValue !== '-') {
            let badgeClass = 'secondary';
            if (displayValue === 'Completed' || displayValue === 'Approved' || displayValue === 'Yes') {
                badgeClass = 'success';
            } else if (displayValue === 'Ongoing' || displayValue === 'In Progress' || displayValue === 'Pending') {
                badgeClass = 'warning';
            } else if (displayValue === 'Overdue') {
                badgeClass = 'danger';
            } else if (displayValue === 'Returned') {
                badgeClass = 'info';
            }
            displayValue = `<span class="badge badge-${badgeClass}">${displayValue}</span>`;
        }
        
        const ynFields = ['complete_case_folder', 'applicable_draft_order', 'first_order_dismissal_cnpc', 'tavable_less_than_10_workers', 'with_deposited_monetary_claims', 'with_order_payment_notice', 'updated_ticked_in_mis'];
        if (ynFields.includes(field) && displayValue !== '-') {
            const badgeClass = (displayValue === 'Y' || displayValue === '1') ? 'success' : 'warning';
            const displayText = (displayValue === 'Y' || displayValue === '1') ? 'Yes' : 'No';
            displayValue = `<span class="badge badge-${badgeClass}">${displayText}</span>`;
        }
        
        if (field === 'establishment_name' && displayValue !== '-') {
            if (displayValue.length > 25) {
                displayValue = displayValue.substring(0, 25) + '...';
            }
        }
        
        cell.html(displayValue);
        cell.removeClass('edit-mode');
    });
}

    function restoreButtons(saveBtn, cancelBtn, originalContent) {
        saveBtn.html(originalContent).prop('disabled', false);
        cancelBtn.prop('disabled', false);
    }

    function restoreActionButtons(row) {
        const actionsCell = row.find('td:last');
        actionsCell.html(actionsCell.data('original-buttons'));
    }

    function cancelEdit() {
        if (!currentEditingRow) return;
        
        const config = getTabConfig(currentTab);
        
        currentEditingRow.find('.editable-cell:not(.readonly-cell)').each(function() {
            const cell = $(this);
            const field = cell.data('field');
            let displayValue = originalData[field] || '';
            
            if (field === 'current_stage' && displayValue.includes(': ')) {
                displayValue = displayValue.split(': ')[1];
            }
            
            if (field === 'establishment_name' && displayValue.length > 25) {
                cell.attr('title', displayValue);
                displayValue = displayValue.substring(0, 25) + '...';
            }
            
            cell.html(displayValue);
            cell.removeClass('edit-mode');
        });
        
        restoreActionButtons(currentEditingRow);
        resetEditState();
    }

    function resetEditState() {
        currentEditingRow = null;
        originalData = {};
        currentTab = null;
    }

    function showAlert(message, type) {
        const currentTabId = getCurrentTab();
        const config = getTabConfig(currentTabId);
        const alertId = type === 'success' ? `success-alert-${config.alertPrefix}` : `error-alert-${config.alertPrefix}`;
        const messageId = type === 'success' ? `success-message-${config.alertPrefix}` : `error-message-${config.alertPrefix}`;
        
        const finalAlertId = $(`#${alertId}`).length ? alertId : (type === 'success' ? 'success-alert' : 'error-alert');
        const finalMessageId = $(`#${messageId}`).length ? messageId : (type === 'success' ? 'success-message' : 'error-message');
        
        $(`#${finalMessageId}`).text(message);
        $(`#${finalAlertId}`).removeClass('fade').addClass('show').show();
        
        setTimeout(() => hideAlert(finalAlertId), 5000);
    }

    function handleAjaxError(xhr, defaultMessage) {
        let errorMessage = defaultMessage;
        
        try {
            if (xhr.responseJSON) {
                if (xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                } else if (xhr.responseJSON.errors) {
                    const errors = Object.values(xhr.responseJSON.errors).flat();
                    errorMessage = 'Validation errors: ' + errors.join(', ');
                }
            }
        } catch (parseError) {
            console.warn('Could not parse error response:', parseError);
        }
        
        if (xhr.status === 404) {
            errorMessage = 'Record not found.';
        } else if (xhr.status === 422) {
            errorMessage = errorMessage.includes('Validation') ? errorMessage : 'Validation error. Please check your input.';
        } else if (xhr.status === 500) {
            errorMessage = 'Server error occurred. Please try again.';
        } else if (xhr.status === 419) {
            errorMessage = 'Session expired. Please refresh the page and try again.';
        }
        
        showAlert(errorMessage, 'danger');
    }

    window.hideAlert = function(alertId) {
        $(`#${alertId}`).removeClass('show').addClass('fade');
        setTimeout(() => $(`#${alertId}`).hide(), 150);
    };

    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        if (currentEditingRow) {
            cancelEdit();
        }
    });
});

// View Document History Button Handler
$(document).on('click', '.view-history-btn', function(e) {
    e.preventDefault();
    const caseId = $(this).data('case-id');
    const caseNo = $(this).data('case-no');
    const establishment = $(this).data('establishment');
    
    console.log('View History clicked - caseId:', caseId);
    
    if (!caseId) {
        showAlert('error', 'Invalid case ID');
        return;
    }
    
    // Set modal header info
    $('#historyCaseNo').text(caseNo);
    $('#historyEstablishment').text(establishment);
    
    // Show modal with loading state
    $('#caseHistoryModal').modal('show');
    $('#historyContent').html(`
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
                <span class="sr-only">Loading...</span>
            </div>
            <p class="text-muted mt-2">Loading document history...</p>
        </div>
    `);
    
    // Load history via AJAX
    $.ajax({
        url: `/case/${caseId}/document-history`,
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            'Accept': 'application/json'
        },
        success: function(response) {
            console.log('History response:', response);
            
            if (response.success) {
                if (response.has_tracking) {
                    displayHistory(response.history);
                } else {
                    $('#historyContent').html(`
                        <div class="alert alert-info text-center">
                            <i class="fas fa-info-circle fa-2x mb-3 d-block"></i>
                            <p class="mb-0">No document tracking history available for this case.</p>
                            <small class="text-muted">Documents have not been transferred yet.</small>
                        </div>
                    `);
                }
            } else {
                $('#historyContent').html(`
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i> 
                        ${response.message || 'Failed to load history'}
                    </div>
                `);
            }
        },
        error: function(xhr) {
            console.error('History load error:', xhr);
            let errorMsg = 'Failed to load document history.';
            if (xhr.responseJSON?.message) {
                errorMsg = xhr.responseJSON.message;
            }
            $('#historyContent').html(`
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i> ${errorMsg}
                </div>
            `);
        }
    });
});

// Helper function to display history timeline
function displayHistory(historyData) {
    if (!historyData || historyData.length === 0) {
        $('#historyContent').html(`
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> No transfer history available yet.
            </div>
        `);
        return;
    }
    
    let timelineHtml = '<div class="timeline" style="position: relative; padding-left: 30px;">';
    timelineHtml += '<div style="content: \'\'; position: absolute; left: 10px; top: 0; bottom: 0; width: 2px; background: #e3e6f0;"></div>';
    
    historyData.forEach((item, index) => {
        const roleClass = item.role ? item.role.toLowerCase().replace(/\s+/g, '_') : '';
        const statusClass = item.status === 'Received' ? 'success' : 'warning';
        
        timelineHtml += `
            <div class="timeline-item" style="position: relative; margin-bottom: 1.5rem;">
                <div style="content: ''; position: absolute; left: -24px; top: 5px; width: 12px; height: 12px; border-radius: 50%; background: #4e73df; border: 2px solid white; box-shadow: 0 0 0 2px #e3e6f0;"></div>
                <div class="card mb-0">
                    <div class="card-body py-3">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <span class="badge badge-${statusClass}" style="font-size: 0.85rem; padding: 0.5rem 1rem;">
                                    ${item.role}
                                </span>
                                ${item.from_role ? '<small class="text-muted ml-2">from ' + item.from_role + '</small>' : ''}
                            </div>
                            <div class="text-right">
                                <small class="text-muted"><i class="fas fa-clock"></i> ${item.time_ago}</small>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <small class="text-muted d-block">Transferred By:</small>
                                <strong>${item.transferred_by}</strong>
                                <br>
                                <small class="text-muted">${item.transferred_at}</small>
                            </div>
                            <div class="col-md-6">
                                <small class="text-muted d-block">Received By:</small>
                                <strong class="${item.received_by === 'Pending' || item.received_by === 'Not Received' ? 'text-warning' : 'text-success'}">
                                    ${item.received_by}
                                </strong>
                                <br>
                                <small class="text-muted">${item.received_at}</small>
                            </div>
                        </div>
                        ${item.notes ? '<hr class="my-2"><small class="text-muted"><i class="fas fa-sticky-note"></i> <strong>Notes:</strong> ' + item.notes + '</small>' : ''}
                    </div>
                </div>
            </div>
        `;
    });
    
    timelineHtml += '</div>';
    $('#historyContent').html(timelineHtml);
}
</script>
@endpush