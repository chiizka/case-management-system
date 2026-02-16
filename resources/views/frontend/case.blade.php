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
.actions-cell {
    padding: 0.25rem 0.5rem !important;
    white-space: nowrap;
    vertical-align: middle;
    overflow: hidden;
}

.actions-cell.collapsed {
    width: 60px !important;
    min-width: 60px !important;
    max-width: 60px !important;
}

.actions-cell.expanded {
    width: auto !important;
    min-width: 200px !important;   /* ← make this wider than your longest button row */
    max-width: 320px !important;
}

.dataTable td.actions-cell,
.dataTable th:last-child {
    box-sizing: border-box !important;
}

/* Help prevent header/body desync */
.table-container {
    overflow-x: auto !important;
    -webkit-overflow-scrolling: touch;
}

/* Container for all buttons in one line */
.action-buttons-container {
    display: flex;
    align-items: center;
    gap: 0.25rem;
    flex-wrap: nowrap;
}

/* Toggle button - always visible */
.action-toggle-btn {
    background: #007bff;
    color: white;
    border: none;
    border-radius: 4px;
    padding: 0.3rem 0.5rem;
    cursor: pointer;
    font-size: 0.75rem;
    transition: background 0.2s;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 32px;
    height: 28px;
    flex-shrink: 0;
}

.action-toggle-btn:hover {
    background: #0056b3;
}

.action-toggle-btn i {
    font-size: 0.7rem;
    margin: 0;
}

/* Action buttons wrapper - hidden when collapsed */
.action-buttons {
    display: none;
    gap: 0.25rem;
    align-items: center;
    flex-wrap: nowrap;
}

.actions-cell.expanded .action-buttons {
    display: flex;
}

.actions-cell.collapsed .action-buttons {
    display: none;
}


.document-item-actions {
    display: flex;
    gap: 5px;
    align-items: center;
}

.file-info {
    font-size: 0.75rem;
    color: #6c757d;
    margin-top: 0.25rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.file-info i {
    color: #28a745;
}

.upload-btn {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}

.list-group-item {
    padding: 0.75rem 1rem;
}

.document-content {
    flex: 1;
    min-width: 0;
}

.readonly-cell {
    background-color: #f8f9fa !important;
    color: #6c757d;
    cursor: not-allowed;
    font-style: italic;
}

.readonly-cell:hover {
    background-color: #e9ecef !important;
}

/* Add to your <style> section */
.readonly-cell::after {
    content: " 🔄";
    font-size: 0.7rem;
    opacity: 0.5;
    margin-left: 3px;
}

.readonly-cell {
    background-color: #f8f9fa !important;
    color: #495057;
    font-style: italic;
}

/* Smooth transition for computed field updates */
.editable-cell, .readonly-cell {
    transition: background-color 0.3s ease, color 0.3s ease;
}

</style>

<!-- Main Content -->
<div id="content">
    <!-- Begin Page Content -->
    <div class="container-fluid">
        {{-- <!-- Tabs Navigation -->
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
        </ul> --}}

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

                        <button class="btn btn-info btn-sm mr-2" id="exportActiveCasesXlsx">
                            <i class="fas fa-file-excel"></i> Export Active Cases (XLSX)
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
                                <th>Actions</th>
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
                                <th>PCT(96 days from the date of NR)</th>
                                <th>Date Signed (MIS)</th>
                                <th>Status (PCT)</th>
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
                            </tr>
                        </thead>
                        <tbody>
                            @if(isset($cases) && $cases->count() > 0)
                                @foreach($cases as $case)
                                    <tr data-id="{{ $case->id }}">
                                        <td class="actions-cell collapsed">
                                            <div class="action-buttons-container">
                                                <button class="action-toggle-btn" type="button">
                                                    <i class="fas fa-chevron-right"></i>
                                                </button>
                                                <div class="action-buttons">
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
                                                    
                                                    <button type="button" 
                                                            class="btn btn-primary btn-sm document-checklist-btn" 
                                                            data-case-id="{{ $case->id }}"
                                                            data-case-no="{{ $case->case_no ?? 'N/A' }}"
                                                            data-establishment="{{ $case->establishment_name ?? 'N/A' }}"
                                                            title="Document Checklist">
                                                        <i class="fas fa-file-alt"></i>
                                                    </button>
                                                    
                                                    @if(Auth::user()->isProvince())
                                                        <button type="button" 
                                                                class="btn btn-warning btn-sm dispose-case-btn" 
                                                                data-case-id="{{ $case->id }}"
                                                                data-case-no="{{ $case->case_no ?? 'N/A' }}"
                                                                data-establishment="{{ $case->establishment_name ?? 'N/A' }}"
                                                                data-stage="{{ explode(': ', $case->current_stage)[1] ?? $case->current_stage ?? 'Unknown' }}"
                                                                title="Mark as Disposed">
                                                            <i class="fas fa-archive"></i>
                                                        </button>
                                                    @else
                                                        <button type="button" 
                                                                class="btn btn-success btn-sm complete-case-btn" 
                                                                data-case-id="{{ $case->id }}"
                                                                data-case-no="{{ $case->case_no ?? 'N/A' }}"
                                                                data-establishment="{{ $case->establishment_name ?? 'N/A' }}"
                                                                data-stage="{{ explode(': ', $case->current_stage)[1] ?? $case->current_stage ?? 'Unknown' }}"
                                                                title="Mark as Complete">
                                                            <i class="fas fa-check-circle"></i>
                                                        </button>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
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
                                        <td class="readonly-cell" data-field="lapse_20_day_period">{{ $case->lapse_20_day_period ?? '-' }}</td>
                                        
                                        <!-- Docketing Stage -->
                                        <td class="readonly-cell" data-field="pct_for_docketing">{{ $case->pct_for_docketing ?? '-' }}</td>
                                        <td class="editable-cell" data-field="date_scheduled_docketed" data-type="date">
                                            {{ $case->date_scheduled_docketed ? \Carbon\Carbon::parse($case->date_scheduled_docketed)->format('Y-m-d') : '-' }}
                                        </td>
                                        <td class="readonly-cell" data-field="aging_docket">{{ $case->aging_docket ?? '-' }}</td>
                                        <td class="readonly-cell" data-field="status_docket">{{ $case->status_docket ?? '-' }}</td>
                                        <td class="editable-cell" data-field="hearing_officer_mis" title="{{ $case->hearing_officer_mis ?? '' }}">
                                            {{ $case->hearing_officer_mis ? Str::limit($case->hearing_officer_mis, 20) : '-' }}
                                        </td>
                                        
                                        <!-- Hearing Process Stage -->
                                        <td class="editable-cell" data-field="date_1st_mc_actual" data-type="date">
                                            {{ $case->date_1st_mc_actual ? \Carbon\Carbon::parse($case->date_1st_mc_actual)->format('Y-m-d') : '-' }}
                                        </td>
                                        <td class="readonly-cell" data-field="first_mc_pct">{{ $case->first_mc_pct ?? '-' }}</td>
                                        <td class="readonly-cell" data-field="status_1st_mc">{{ $case->status_1st_mc ?? '-' }}</td>
                                        <td class="editable-cell" data-field="date_2nd_last_mc" data-type="date">
                                            {{ $case->date_2nd_last_mc ? \Carbon\Carbon::parse($case->date_2nd_last_mc)->format('Y-m-d') : '-' }}
                                        </td>
                                        <td class="readonly-cell" data-field="second_last_mc_pct">{{ $case->second_last_mc_pct ?? '-' }}</td>
                                        <td class="readonly-cell" data-field="status_2nd_mc">{{ $case->status_2nd_mc ?? '-' }}</td>
                                        <td class="editable-cell" data-field="case_folder_forwarded_to_ro">{{ $case->case_folder_forwarded_to_ro ?? '-' }}</td>
                                        <td class="editable-cell" data-field="draft_order_from_po_type">{{ $case->draft_order_from_po_type ?? '-' }}</td>
                                        <td class="editable-cell" data-field="applicable_draft_order">{{ $case->applicable_draft_order ?? '-' }}</td>
                                        <td class="editable-cell" data-field="complete_case_folder">{{ $case->complete_case_folder ?? '-' }}</td>
                                        <td class="editable-cell" data-field="twg_ali">{{ $case->twg_ali ?? '-' }}</td>
                                        
                                        <!-- Review & Drafting Stage -->
                                        <td class="readonly-cell" data-field="po_pct">{{ $case->po_pct ?? '-' }}</td>
                                        <td class="readonly-cell" data-field="aging_po_pct">{{ $case->aging_po_pct ?? '-' }}</td>
                                        <td class="readonly-cell" data-field="status_po_pct">{{ $case->status_po_pct ?? '-' }}</td>
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
                                    <label for="inspection_id">Inspection ID <span class="text-danger">*</span></label>
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
                            <label for="establishment_name">Establishment Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="establishment_name" name="establishment_name" placeholder="Enter establishment name" required>
                        </div>

                        {{-- ✨ NEW: PO Office Field --}}
                        <div class="form-group">
                            <label for="po_office">Provincial Office <span class="text-danger">*</span></label>
                            
                            @if(Auth::user()->isProvince())
                                {{-- Province users: auto-filled and read-only --}}
                                <input type="text" 
                                    class="form-control" 
                                    id="po_office" 
                                    name="po_office"
                                    value="{{ Auth::user()->getProvinceName() }}"
                                    readonly
                                    style="background-color: #e9ecef; cursor: not-allowed;">
                                <small class="form-text text-muted">
                                    <i class="fas fa-info-circle"></i> Automatically set to your province office
                                </small>
                            @else
                                {{-- Regional office users: dropdown selection --}}
                                <select class="form-control" id="po_office" name="po_office" required>
                                    <option value="">Select Provincial Office</option>
                                    <option value="Albay">Albay</option>
                                    <option value="Camarines Sur">Camarines Sur</option>
                                    <option value="Camarines Norte">Camarines Norte</option>
                                    <option value="Catanduanes">Catanduanes</option>
                                    <option value="Masbate">Masbate</option>
                                    <option value="Sorsogon">Sorsogon</option>
                                </select>
                                <small class="form-text text-muted">
                                    <i class="fas fa-info-circle"></i> Select the provincial office where this case originated
                                </small>
                            @endif
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
                                        <option value="Appealed">Appealed</option>
                                        <option value="Disposed">Disposed</option>
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

    <!-- Archive/Dispose Case Modal -->
    <div class="modal fade" id="stageProgressionModal" tabindex="-1" role="dialog" aria-labelledby="stageProgressionModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header" id="modalHeader">
                    <h5 class="modal-title" id="stageProgressionModalLabel">
                        <i class="fas fa-archive mr-2"></i>
                        <span id="modalTitleText">Archive Case</span>
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="alert" id="modalAlertBox" role="alert">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        <strong>Warning!</strong> This action cannot be undone.
                    </div>
                    
                    <p id="stageProgressionMessage"></p>
                    
                    <div class="card bg-light">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-12 mb-2">
                                    <strong>Case No:</strong> <span id="stageCaseInfo" class="text-primary"></span>
                                </div>
                                <div class="col-12 mb-2" id="currentStageRow">
                                    <strong>Current Stage:</strong> <span id="stageCurrentStage" class="badge badge-info"></span>
                                </div>
                                <div class="col-12">
                                    <strong>Will be marked as:</strong> <span id="stageNextStage"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-2"></i>Cancel
                    </button>
                    <button type="button" class="btn btn-primary" id="confirmStageBtn">
                        <i class="fas fa-check mr-2"></i>Confirm
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

    <!-- Document Checklist Modal -->
    <div class="modal fade" id="documentChecklistModal" tabindex="-1" role="dialog" aria-labelledby="documentChecklistModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="documentChecklistModalLabel">
                        <i class="fas fa-file-alt"></i> Document Checklist
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="case-info mb-3">
                        <p class="mb-1"><strong>Case No:</strong> <span id="checklist-case-no"></span></p>
                        <p class="mb-0"><strong>Establishment:</strong> <span id="checklist-establishment"></span></p>
                    </div>
                    
                    <hr>
                    
                    <div class="add-document-section mb-3">
                        <div class="input-group">
                            <input type="text" 
                                class="form-control" 
                                id="newDocumentTitle" 
                                placeholder="Enter document title">
                            <div class="input-group-append">
                                <button class="btn btn-primary" type="button" id="addDocumentBtn">
                                    <i class="fas fa-plus"></i> Add Document
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="documents-list">
                        <h6 class="mb-3">Documents:</h6>
                        <ul class="list-group" id="documentsList">
                            <!-- Documents will be added here dynamically -->
                        </ul>
                        <p class="text-muted text-center mt-3" id="noDocumentsMessage">
                            No documents added yet.
                        </p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
 
    <input type="file" id="documentFileInput" style="display: none;" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.xlsx,.xls">


    <!-- Export Options Modal (Pure Client-Side) -->
    <div class="modal fade" id="exportOptionsModal" tabindex="-1" role="dialog" aria-labelledby="exportModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title" id="exportModalLabel">
                        <i class="fas fa-file-excel mr-2"></i> Export Active Cases to XLSX
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><strong>Scope:</strong></label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="exportScope" id="scopeFiltered" value="filtered" checked>
                                    <label class="form-check-label" for="scopeFiltered">
                                        Current view (<span id="filteredCount">0</span> rows)
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="exportScope" id="scopeAll" value="all">
                                    <label class="form-check-label" for="scopeAll">
                                        All active cases (<span id="allCount">0</span> rows)
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><strong>Filter by Created Year:</strong></label>
                                <select class="form-control" id="exportYear">
                                    <option value="">All years</option>
                                    <option value="2026">2026</option>
                                    <option value="2025">2025</option>
                                    <option value="2024">2024</option>
                                    <option value="2023">2023</option>
                                    <option value="2022">2022</option>
                                </select>
                                <small class="form-text text-muted">Based on "Created At" date</small>
                            </div>
                        </div>
                    </div>
                    
                    
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success btn-lg" id="confirmExportBtn">
                        <i class="fas fa-download mr-2"></i> Download XLSX
                    </button>
                </div>
            </div>
        </div>
    </div>

@endsection
@push('scripts')
<!-- DataTables plugins -->
<script src="{{ asset('vendor/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('vendor/datatables/dataTables.bootstrap4.min.js') }}"></script>
<!-- SheetJS (xlsx full version) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

<!-- FileSaver.js - for triggering the browser download -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.5/FileSaver.min.js"></script>

<script>

$(document).ready(function() {
    console.log('jQuery version:', $.fn.jquery);
    console.log('DataTables available:', typeof $.fn.DataTable !== 'undefined');
    
    // Initialize DataTable with Actions column excluded from sorting
    $('#dataTable0').DataTable({
        columnDefs: [
            { 
                orderable: false, 
                targets: 0  // First column (Actions)
            }
        ]
    });

    // Block double-click editing on readonly cells
    $(document).on('dblclick', '.readonly-cell', function(e) {
        e.preventDefault();
        e.stopPropagation();
        // Optional: visual feedback
        $(this).css('background-color', '#fff3cd');
        setTimeout(() => $(this).css('background-color', '#f8f9fa'), 400);
    });

let documents = [];
let currentCaseId = null;
let currentUploadDocId = null;

    // Prevent editing of computed fields
    $(document).on('click', '.readonly-cell', function(e) {
        e.stopPropagation();
        e.preventDefault();
        
        // Visual feedback
        const $this = $(this);
        const originalBg = $this.css('background-color');
        $this.css('background-color', '#fff3cd');
        setTimeout(() => {
            $this.css('background-color', originalBg);
        }, 300);
    });

// 2. DOCUMENT CHECKLIST BUTTON CLICK HANDLER (from your first script)
$(document).on('click', '.document-checklist-btn', function() {
    currentCaseId = $(this).data('case-id');
    const caseNo = $(this).data('case-no');
    const establishment = $(this).data('establishment');
    
    $('#checklist-case-no').text(caseNo);
    $('#checklist-establishment').text(establishment);
    $('#newDocumentTitle').val('');
    
    documents = [];
    loadDocuments();
    $('#documentChecklistModal').modal('show');
});

// 3. ADD DOCUMENT BUTTON
$('#addDocumentBtn').on('click', function() {
    const title = $('#newDocumentTitle').val().trim();
    
    if (title === '') {
        alert('Please enter a document title');
        return;
    }
    
    documents.push({
        id: Date.now(),
        title: title,
        checked: false
    });
    
    $('#newDocumentTitle').val('');
    saveDocuments();
    renderDocuments();
});

// 4. CHECKBOX CHANGE HANDLER
$(document).on('change', '.document-checkbox', function() {
    const docId = parseInt($(this).data('doc-id'));
    const isChecked = $(this).is(':checked');
    
    console.log('Checkbox changed:', docId, 'checked:', isChecked, typeof isChecked);
    
    const doc = documents.find(d => d.id == docId);
    if (doc) {
        doc.checked = isChecked;
        console.log('Updated document:', doc);
        
        // Update UI immediately BEFORE saving
        const $label = $(`label[for="doc-${doc.id}"]`);
        const $fileInfo = $label.closest('.document-content').find('.file-info');
        
        if (isChecked) {
            $label.addClass('text-muted').css('text-decoration', 'line-through');
            $fileInfo.addClass('text-muted').css('text-decoration', 'line-through');
        } else {
            $label.removeClass('text-muted').css('text-decoration', 'none');
            $fileInfo.removeClass('text-muted').css('text-decoration', 'none');
        }
        
        // Save to server
        saveDocuments();
    }
});

// 5. REMOVE DOCUMENT BUTTON
$(document).on('click', '.remove-document-btn', function() {
    const docId = parseInt($(this).data('doc-id'));
    
    const doc = documents.find(d => d.id == docId);
    const hasFile = doc && doc.file_path;
    
    // Different confirmation messages based on whether file exists
    let confirmMessage = hasFile 
        ? 'This document has an uploaded file. Remove document and delete the file?' 
        : 'Remove this document from checklist?';
    
    if (!confirm(confirmMessage)) {
        return;
    }
    
    console.log('Removing document:', docId);
    
    // If document has file, delete it first from server
    if (hasFile) {
        // Delete file from server without waiting for response
        $.ajax({
            url: `/case/${currentCaseId}/documents/${docId}/file`,
            method: 'DELETE',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                console.log('File deleted from server:', response);
            },
            error: function(xhr) {
                console.log('File delete error (may already be deleted):', xhr);
                // Don't show error to user - file might already be gone
            }
        });
    }
    
    // Remove document from array
    documents = documents.filter(d => d.id != docId);
    console.log('Documents after removal:', documents);
    
    // Save and re-render
    saveDocuments();
    renderDocuments();
});

// 6. UPLOAD FILE BUTTON
$(document).on('click', '.upload-file-btn', function() {
    currentUploadDocId = parseInt($(this).data('doc-id'));
    $('#documentFileInput').click();
});

// 7. FILE INPUT CHANGE HANDLER
$('#documentFileInput').on('change', function(e) {
    const file = e.target.files[0];
    
    if (!file) {
        return;
    }
    
    // Validate file size (10MB max)
    if (file.size > 10 * 1024 * 1024) {
        alert('File size must be less than 10MB');
        $(this).val('');
        return;
    }
    
    // Validate file type
    const allowedExtensions = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'xlsx', 'xls'];
    const fileExtension = file.name.split('.').pop().toLowerCase();
    
    if (!allowedExtensions.includes(fileExtension)) {
        alert('Invalid file type. Allowed: PDF, DOC, DOCX, JPG, PNG, XLSX, XLS');
        $(this).val('');
        return;
    }
    
    uploadDocumentFile(currentUploadDocId, file);
});

// 8. VIEW/DOWNLOAD FILE BUTTON
$(document).on('click', '.view-file-btn', function() {
    const docId = parseInt($(this).data('doc-id'));
    viewDocumentFile(docId);
});

// 9. DELETE FILE BUTTON
$(document).on('click', '.delete-file-btn', function() {
    const docId = parseInt($(this).data('doc-id'));
    
    if (!confirm('Delete this uploaded file?')) {
        return;
    }
    
    deleteDocumentFile(docId);
});

// 10. ENTER KEY HANDLER
$('#newDocumentTitle').on('keypress', function(e) {
    if (e.which === 13) {
        $('#addDocumentBtn').click();
    }
});

    // 11. LOAD DOCUMENTS FUNCTION 
    function loadDocuments() {
        // Define the 15 required documents
        const requiredDocuments = [
            'Authority to Inspect',
            'Affidavit',
            'Labor Inspection Checklist',
            'Notice of Inspection Result',
            'Inspection Evaluation and Action Sheet',
            'List of Establishments, Affected Employees, and Contact Number',
            'Notice of Mandatory Conference',
            'Payroll',
            'Minutes of the Conference / Hearing',
            'Documentary Attachment Checklist',
            '1st Order / 2nd Order / Notice of Order',
            'Post-Evaluation Checklist',
            'Notice of Finality',
            '2nd Order CNPC',
            'Compliance Documents'
        ];
        
        $.ajax({
            url: `/case/${currentCaseId}/documents`,
            method: 'GET',
            success: function(response) {
                console.log('Loaded documents from DB:', response.documents);
                
                if (response.success && response.documents && response.documents.length > 0) {
                    // Use existing documents from database
                    documents = response.documents.map(doc => ({
                        ...doc,
                        checked: doc.checked === true || doc.checked === 'true' || doc.checked === 1
                    }));
                } else {
                    // Initialize with required documents if none exist
                    documents = requiredDocuments.map((title, index) => ({
                        id: Date.now() + index,
                        title: title,
                        checked: false
                    }));
                    
                    // Save the initial required documents
                    saveDocuments();
                }
                
                console.log('Processed documents:', documents);
                renderDocuments();
            },
            error: function(xhr) {
                console.error('Load error:', xhr);
            }
        });
    }

// 12. SAVE DOCUMENTS FUNCTION
function saveDocuments() {
    console.log('Saving documents to DB:', documents);
    
    $.ajax({
        url: `/case/${currentCaseId}/documents`,
        method: 'POST',
        data: {
            documents: documents,
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            console.log('Save response:', response);
        },
        error: function(xhr) {
            console.error('Save error:', xhr);
            alert('Failed to save. Please try again.');
        }
    });
}

// 13. UPLOAD FILE FUNCTION
function uploadDocumentFile(docId, file) {
    const formData = new FormData();
    formData.append('file', file);
    formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
    
    // Show loading state
    const $uploadBtn = $(`.upload-file-btn[data-doc-id="${docId}"]`);
    const originalHtml = $uploadBtn.html();
    $uploadBtn.html('<i class="fas fa-spinner fa-spin"></i>').prop('disabled', true);
    
    $.ajax({
        url: `/case/${currentCaseId}/documents/${docId}/upload`,
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            if (response.success) {
                // Update document with file info
                const doc = documents.find(d => d.id == docId);
                if (doc) {
                    doc.file_name = response.file_name;
                    doc.file_size = response.file_size;
                    doc.uploaded_at = response.uploaded_at;
                    doc.file_path = 'uploaded'; // Flag that file exists
                }
                
                // Clear file input
                $('#documentFileInput').val('');
                
                // Re-render documents
                renderDocuments();
                
                // Show success message
                showToast('success', response.message);
            } else {
                alert(response.message || 'Upload failed');
                $uploadBtn.html(originalHtml).prop('disabled', false);
            }
        },
        error: function(xhr) {
            console.error('Upload error:', xhr);
            const errorMsg = xhr.responseJSON?.message || 'Failed to upload file';
            alert(errorMsg);
            $uploadBtn.html(originalHtml).prop('disabled', false);
        }
    });
}

// 14. VIEW FILE FUNCTION (opens in new tab)
function viewDocumentFile(docId) {
    const url = `/case/${currentCaseId}/documents/${docId}/download`;
    window.open(url, '_blank');
}

// 15. DELETE FILE FUNCTION
function deleteDocumentFile(docId) {
    $.ajax({
        url: `/case/${currentCaseId}/documents/${docId}/file`,
        method: 'DELETE',
        data: {
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success) {
                // Update document - remove file info
                const doc = documents.find(d => d.id == docId);
                if (doc) {
                    delete doc.file_name;
                    delete doc.file_size;
                    delete doc.uploaded_at;
                    delete doc.uploaded_by;
                    delete doc.file_path;
                }
                
                // Re-render documents
                renderDocuments();
                
                showToast('success', response.message);
            }
        },
        error: function(xhr) {
            console.error('Delete error:', xhr);
            
            // Only show error if it's not a 404 (file already deleted)
            if (xhr.status !== 404) {
                alert('Failed to delete file');
            } else {
                console.log('File already deleted or not found - continuing anyway');
                
                // Clean up the document anyway
                const doc = documents.find(d => d.id == docId);
                if (doc) {
                    delete doc.file_name;
                    delete doc.file_size;
                    delete doc.uploaded_at;
                    delete doc.uploaded_by;
                    delete doc.file_path;
                }
                
                renderDocuments();
            }
        }
    });
}

// 16. RENDER DOCUMENTS FUNCTION
function renderDocuments() {
    console.log('Rendering documents:', documents);
    
    const docsList = $('#documentsList');
    const noDocsMessage = $('#noDocumentsMessage');
    
    docsList.empty();
    
    if (!documents || documents.length === 0) {
        noDocsMessage.show();
        return;
    }
    
    noDocsMessage.hide();
    
    documents.forEach(doc => {
        const isChecked = doc.checked === true;
        const hasFile = doc.file_path && doc.file_name;
        
        console.log(`Rendering doc ${doc.id}: checked=${doc.checked}, hasFile=${hasFile}`);
        
        let fileInfo = '';
        let uploadButton = '';
        
        if (hasFile) {
            // Show file info and action buttons
            fileInfo = `
                <div class="file-info">
                    <i class="fas fa-paperclip"></i>
                    <span>${doc.file_name} (${doc.file_size || 'Unknown size'})</span>
                    <button class="btn btn-sm btn-outline-primary view-file-btn" 
                            data-doc-id="${doc.id}" 
                            title="View/Download file"
                            type="button">
                        <i class="fas fa-download"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-danger delete-file-btn" 
                            data-doc-id="${doc.id}" 
                            title="Delete file"
                            type="button">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            `;
        } else {
            // Show upload button
            uploadButton = `
                <button class="btn btn-sm btn-success upload-file-btn upload-btn" 
                        data-doc-id="${doc.id}"
                        title="Upload file"
                        type="button">
                    <i class="fas fa-upload"></i> Upload
                </button>
            `;
        }
        
        const item = `
            <li class="list-group-item d-flex justify-content-between align-items-start">
                <div class="document-content">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" 
                               class="custom-control-input document-checkbox" 
                               id="doc-${doc.id}" 
                               data-doc-id="${doc.id}"
                               ${isChecked ? 'checked' : ''}>
                        <label class="custom-control-label ${isChecked ? 'text-muted' : ''}" 
                               for="doc-${doc.id}" 
                               style="${isChecked ? 'text-decoration: line-through;' : ''}">
                            ${doc.title}
                        </label>
                    </div>
                    ${fileInfo}
                </div>
                <div class="document-item-actions">
                    ${uploadButton}
                    <button class="btn btn-sm btn-danger remove-document-btn" 
                            data-doc-id="${doc.id}"
                            title="Remove from checklist"
                            type="button">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </li>
        `;
        docsList.append(item);
    });
}

// 17. TOAST NOTIFICATION FUNCTION
function showToast(type, message) {
    const toastHtml = `
        <div class="toast" role="alert" style="position: fixed; top: 20px; right: 20px; z-index: 9999;">
            <div class="toast-header ${type === 'success' ? 'bg-success' : 'bg-danger'} text-white">
                <strong class="mr-auto">${type === 'success' ? 'Success' : 'Error'}</strong>
                <button type="button" class="ml-2 mb-1 close text-white" data-dismiss="toast">
                    <span>&times;</span>
                </button>
            </div>
            <div class="toast-body">
                ${message}
            </div>
        </div>
    `;
    
    $('body').append(toastHtml);
    $('.toast').toast({ delay: 3000 }).toast('show');
    
    $('.toast').on('hidden.bs.toast', function() {
        $(this).remove();
    });
}

// ==========================================
// ACTION TOGGLE BUTTONS (from your first script)
// ==========================================

$(document).on('click', '.action-toggle-btn', function(e) {
    e.preventDefault();
    e.stopPropagation();

    const $btn = $(this);
    const $cell = $btn.closest('.actions-cell');
    const $row = $cell.closest('tr');
    const $table = $row.closest('table');
    const dt = $table.DataTable();

    const isExpanding = $cell.hasClass('collapsed');

    // Toggle classes
    $cell.toggleClass('collapsed expanded');
    $btn.find('i')
        .toggleClass('fa-chevron-right fa-chevron-left');

    setTimeout(() => {
        dt.columns.adjust().draw(false);
        $table.css('table-layout', 'auto');
        dt.columns.adjust().draw(false);
        $table.css('table-layout', 'fixed');

        const $container = $table.closest('.table-container');
        $container.scrollLeft($container.scrollLeft() + 1);
        $container.scrollLeft($container.scrollLeft() - 1);
    }, 20);
});

$(document).on('click', '.edit-row-btn-case', function(e) {
    e.preventDefault();
    e.stopPropagation();

    const $row = $(this).closest('tr');
    const $cell = $row.find('.actions-cell');

    if ($cell.hasClass('collapsed')) {
        $cell.removeClass('collapsed').addClass('expanded');
        $cell.find('.action-toggle-btn i')
            .removeClass('fa-chevron-right')
            .addClass('fa-chevron-left');

        setTimeout(() => {
            const dt = $row.closest('table').DataTable();
            dt.columns.adjust().draw(false);
            const $table = $row.closest('table');
            $table.css('table-layout', 'auto');
            dt.columns.adjust().draw(false);
            $table.css('table-layout', 'fixed');

            const $container = $table.closest('.table-container');
            $container.scrollLeft($container.scrollLeft() + 1);
            $container.scrollLeft($container.scrollLeft() - 1);
        }, 30);
    }
});

// Collapse when clicking outside
$(document).on('click', function(e) {
    if (
        $(e.target).closest('.actions-cell').length ||
        $(e.target).closest('.save-btn-case, .cancel-btn-case, .edit-row-btn-case').length
    ) {
        return;
    }

    $('.actions-cell.expanded').each(function() {
        const $cell = $(this);
        const $table = $cell.closest('table');
        const dt = $table.DataTable();

        $cell.removeClass('expanded').addClass('collapsed');
        $cell.find('i').removeClass('fa-chevron-left').addClass('fa-chevron-right');

        setTimeout(() => {
            dt.columns.adjust();
            dt.draw(false);
            const containerWidth = $table.closest('.table-container').width();
            $table.find('thead').css('width', containerWidth + 'px');
        }, 30);
    });
});
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


// Complete case handler (archives case from any stage)
$(document).on('click', '.complete-case-btn', function(e) {
    e.preventDefault();
    const button = $(this);
    const caseId = button.data('case-id');
    
    if (!caseId) {
        console.error('No case ID found on button');
        showAlert('error', 'Error: Could not identify case');
        return;
    }
    
    const caseNo = button.data('case-no') || 'N/A';
    const establishment = button.data('establishment') || 'N/A';
    const currentStage = button.data('stage') || 'Unknown';
    
    caseToProgress = {
        id: caseId,
        button: button
    };
    
    // Set modal styling for Complete
    $('#modalHeader').removeClass('bg-warning').addClass('bg-success text-white');
    $('#modalTitleText').text('Complete Case');
    $('#modalAlertBox').removeClass('alert-warning').addClass('alert-success');
    
    const message = `
        <strong>Mark this case as Completed?</strong><br>
        <small class="text-muted">This case will be permanently moved to archived cases.</small>
    `;
    
    $('#stageProgressionMessage').html(message);
    $('#stageCaseInfo').text(`${caseNo} - ${establishment}`);
    $('#stageCurrentStage').text(currentStage);
    $('#stageNextStage').html('<span class="badge badge-success"><i class="fas fa-check-circle mr-1"></i>Completed</span>');
    
    // Update confirm button
    $('#confirmStageBtn')
        .removeClass('btn-warning')
        .addClass('btn-success')
        .html('<i class="fas fa-check mr-2"></i>Confirm Complete');
    
    $('#stageProgressionModal').modal('show');
});

// Dispose case handler (for province users only)
$(document).on('click', '.dispose-case-btn', function(e) {
    e.preventDefault();
    const button = $(this);
    const caseId = button.data('case-id');
    
    if (!caseId) {
        console.error('No case ID found on button');
        showAlert('error', 'Error: Could not identify case');
        return;
    }
    
    const caseNo = button.data('case-no') || 'N/A';
    const establishment = button.data('establishment') || 'N/A';
    const currentStage = button.data('stage') || 'Unknown';
    
    caseToProgress = {
        id: caseId,
        button: button,
        action: 'dispose'
    };
    
    // Set modal styling for Dispose
    $('#modalHeader').removeClass('bg-success').addClass('bg-warning text-white');
    $('#modalTitleText').text('Dispose Case');
    $('#modalAlertBox').removeClass('alert-success').addClass('alert-warning');
    
    const message = `
        <strong>Mark this case as Disposed?</strong><br>
        <small class="text-muted">This case will be moved to archived cases with "Disposed" status.</small><br>
        <small class="text-info"><i class="fas fa-info-circle"></i> Disposed cases indicate the case was closed at the provincial level.</small>
    `;
    
    $('#stageProgressionMessage').html(message);
    $('#stageCaseInfo').text(`${caseNo} - ${establishment}`);
    $('#stageCurrentStage').text(currentStage);
    $('#stageNextStage').html('<span class="badge badge-warning"><i class="fas fa-archive mr-1"></i>Disposed</span>');
    
    // Update confirm button
    $('#confirmStageBtn')
        .removeClass('btn-success')
        .addClass('btn-warning')
        .html('<i class="fas fa-archive mr-2"></i>Confirm Dispose');
    
    $('#stageProgressionModal').modal('show');
});

$('#confirmStageBtn').off('click').on('click', function() {
    console.log('=== CONFIRM STAGE PROGRESSION ===');
    console.log('caseToProgress:', caseToProgress);
    
    if (!caseToProgress || !caseToProgress.id) {
        console.error('No case selected for progression');
        showAlert('error', 'No case selected');
        return;
    }
    
    const button = $(this);
    const isForceComplete = caseToProgress.button && caseToProgress.button.hasClass('complete-case-btn');
    const isDispose = caseToProgress.action === 'dispose';
    
    console.log('Is Force Complete:', isForceComplete);
    console.log('Is Dispose:', isDispose);
    console.log('Button classes:', caseToProgress.button.attr('class'));
    
    button.prop('disabled', true);
    
    if (isDispose) {
        button.html('<i class="fas fa-spinner fa-spin"></i> Disposing...');
    } else {
        button.html('<i class="fas fa-spinner fa-spin"></i> Archiving...');
    }
    
    const ajaxData = {
        _token: '{{ csrf_token() }}',
        force_complete: isForceComplete,
        dispose: isDispose
    };
    
    console.log('Sending AJAX with data:', ajaxData);
    console.log('URL:', `/case/${caseToProgress.id}/archive`);  // ← UPDATED HERE
    
    $.ajax({
        url: `/case/${caseToProgress.id}/archive`,  // ← UPDATED HERE
        method: 'POST',
        data: ajaxData,
        success: function(response) {
            console.log('=== SUCCESS RESPONSE ===');
            console.log('Full response:', response);
            
            $('#stageProgressionModal').modal('hide');
            
            if (response.success) {
                showAlert('success', response.message);
                
                if (response.case_id) {
                    console.log('Case ID:', response.case_id);
                    console.log('New Status:', response.new_status);
                }
                
                setTimeout(() => {
                    console.log('Reloading page...');
                    location.href = location.href;
                }, 1500);
            } else {
                showAlert('error', response.message || 'Failed to process case');
            }
        },
        error: function(xhr) {
            console.error('=== ERROR RESPONSE ===');
            console.error('Status:', xhr.status);
            console.error('Response:', xhr.responseJSON);
            console.error('Full XHR:', xhr);
            
            const message = xhr.responseJSON?.message || 'Failed to process case';
            showAlert('error', message);
        },
        complete: function() {
            button.prop('disabled', false).html('<i class="fas fa-check mr-2"></i>Confirm');
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
        let currentEditingCell = null;
    let originalValue = null;

    // Double-click to edit cell
    $(document).on('dblclick', '.editable-cell', function(e) {
        e.preventDefault();
        
        // If already editing another cell, save it first
        if (currentEditingCell && currentEditingCell !== this) {
            saveCell($(currentEditingCell));
        }
        
        startEditing($(this));
    });

    // Function to start editing a cell
    function startEditing($cell) {
        // Don't edit if already in edit mode
        if ($cell.find('input, select, textarea').length > 0) {
            return;
        }

        currentEditingCell = $cell[0];
        const field = $cell.data('field');
        const fieldType = $cell.data('type') || 'text';
        const currentValue = $cell.text().trim();
        
        // Store original value
        originalValue = currentValue === '-' ? '' : currentValue;
        
        // Get the tab configuration
        const $row = $cell.closest('tr');
        const $table = $cell.closest('table');
        const tableId = $table.attr('id');
        
        // Determine which tab config to use
        let tabKey = 'tab0'; // default
        if (tableId === 'dataTable1') tabKey = 'tab1';
        else if (tableId === 'dataTable2') tabKey = 'tab2';
        else if (tableId === 'dataTable3') tabKey = 'tab3';
        else if (tableId === 'dataTable4') tabKey = 'tab4';
        else if (tableId === 'dataTable5') tabKey = 'tab5';
        else if (tableId === 'dataTable6') tabKey = 'tab6';
        else if (tableId === 'dataTable7') tabKey = 'tab7';
        
        const fieldConfig = tabConfigs[tabKey]?.fields[field];
        
        // Create input element based on field type
        let $input;
        
        if (fieldType === 'select' || fieldConfig?.type === 'select') {
            // Create select dropdown
            $input = $('<select class="form-control form-control-sm inline-edit-input"></select>');
            
            if (fieldConfig && fieldConfig.options) {
                fieldConfig.options.forEach(opt => {
                    const selected = opt.value == originalValue || opt.text == originalValue;
                    $input.append(`<option value="${opt.value}" ${selected ? 'selected' : ''}>${opt.text}</option>`);
                });
            }
        } else if (fieldType === 'date' || fieldConfig?.type === 'date') {
            // Create date input
            $input = $('<input type="date" class="form-control form-control-sm inline-edit-input">');
            $input.val(originalValue);
        } else if (fieldType === 'boolean') {
            // Create select for boolean (Yes/No)
            $input = $('<select class="form-control form-control-sm inline-edit-input"></select>');
            $input.append('<option value="">Select</option>');
            $input.append(`<option value="1" ${originalValue === 'Yes' || originalValue === '1' ? 'selected' : ''}>Yes</option>`);
            $input.append(`<option value="0" ${originalValue === 'No' || originalValue === '0' ? 'selected' : ''}>No</option>`);
        } else if (fieldConfig?.type === 'number') {
            // Create number input
            $input = $('<input type="number" class="form-control form-control-sm inline-edit-input">');
            if (fieldConfig.step) {
                $input.attr('step', fieldConfig.step);
            }
            // Remove commas from formatted numbers
            const numValue = originalValue.replace(/,/g, '');
            $input.val(numValue);
        } else {
            // Default text input
            $input = $('<input type="text" class="form-control form-control-sm inline-edit-input">');
            $input.val(originalValue);
        }
        
        // Style the input to fit the cell
        $input.css({
            'width': '100%',
            'padding': '4px 8px',
            'border': '2px solid #4CAF50',
            'box-shadow': '0 0 5px rgba(76, 175, 80, 0.5)'
        });
        
        // Replace cell content with input
        $cell.html($input);
        $input.focus();
        
        // Select text in text inputs
        if ($input.is('input[type="text"], input[type="number"]')) {
            $input.select();
        }
        
        // Handle keyboard events
        $input.on('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                saveCell($cell);
            } else if (e.key === 'Escape') {
                e.preventDefault();
                cancelEdit($cell);
            }
        });
        
        // Handle blur (clicking outside)
        $input.on('blur', function() {
            // Small delay to allow other events to fire first
            setTimeout(() => {
                if (currentEditingCell === $cell[0]) {
                    saveCell($cell);
                }
            }, 200);
        });
    }

    // Function to save cell
    function saveCell($cell) {
        const $input = $cell.find('.inline-edit-input');
        if ($input.length === 0) return;
        
        const newValue = $input.val();
        const field = $cell.data('field');
        const fieldType = $cell.data('type');
        const $row = $cell.closest('tr');
        const recordId = $row.data('id');
        
        // Get the endpoint based on table
        const $table = $cell.closest('table');
        const tableId = $table.attr('id');
        let endpoint = '/case/';
        
        if (tableId === 'dataTable1') endpoint = '/inspection/';
        else if (tableId === 'dataTable2') endpoint = '/docketing/';
        else if (tableId === 'dataTable3') endpoint = '/hearing-process/';
        else if (tableId === 'dataTable4') endpoint = '/review-and-drafting/';
        else if (tableId === 'dataTable5') endpoint = '/orders-and-disposition/';
        else if (tableId === 'dataTable6') endpoint = '/compliance-and-awards/';
        else if (tableId === 'dataTable7') endpoint = '/appeals-and-resolution/';
        
        // If value hasn't changed, just restore original display
        if (newValue === originalValue) {
            restoreCellDisplay($cell, originalValue, fieldType);
            currentEditingCell = null;
            return;
        }
        
        // Show loading state
        $cell.html('<i class="fas fa-spinner fa-spin text-primary"></i>');
        
        // Prepare data
        const updateData = {};
        updateData[field] = newValue;
        
        // Send AJAX request
        $.ajax({
            url: endpoint + recordId + '/inline-update',
            method: 'PUT',
            data: updateData,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    // ✅ NEW: Update ALL cells in the row with fresh data from backend
                    updateRowWithComputedFields($row, response.data, fieldType);
                    
                    // Highlight the cell that was just edited
                    $cell.addClass('bg-success text-white');
                    setTimeout(() => {
                        $cell.removeClass('bg-success text-white');
                    }, 1000);
                    
                    // Show toast notification
                    showToast('Success', 'Updated successfully', 'success');
                } else {
                    // Restore original value on error
                    restoreCellDisplay($cell, originalValue, fieldType);
                    showToast('Error', response.message || 'Update failed', 'error');
                }
                currentEditingCell = null;
            },
            error: function(xhr) {
                // Restore original value on error
                restoreCellDisplay($cell, originalValue, fieldType);
                const errorMsg = xhr.responseJSON?.message || 'Failed to update cell';
                showToast('Error', errorMsg, 'error');
                currentEditingCell = null;
            }
        });
    }

    // ✅ UPDATED FUNCTION: Update all cells in a row with computed values
    function updateRowWithComputedFields($row, data) {
        // Define which fields are computed (read-only)
        const computedFields = [
            'lapse_20_day_period',
            'pct_for_docketing',
            'aging_docket',
            'status_docket',
            'first_mc_pct',
            'status_1st_mc',
            'second_last_mc_pct',
            'status_2nd_mc',
            'po_pct',
            'aging_po_pct',
            'status_po_pct',
            'pct_96_days'
        ];
        
        // Update all cells in the row
        $row.find('.editable-cell, .readonly-cell').each(function() {
            const $cell = $(this);
            const field = $cell.data('field');
            
            // Skip if this field is not in the response data
            if (!(field in data)) return;
            
            let value = data[field];
            const fieldType = $cell.data('type');
            
            // Format the value based on type
            if (value === null || value === undefined || value === '') {
                value = '-';
            } else if (fieldType === 'date' && value !== '-') {
                // ✅ FIX: Parse ISO date and format as YYYY-MM-DD
                value = formatDateFromISO(value);
            } else if (fieldType === 'boolean') {
                value = value ? 'Yes' : 'No';
            } else if (fieldType === 'select' && field === 'current_stage') {
                // Handle current_stage display
                if (value.includes(': ')) {
                    value = value.split(': ')[1];
                }
            }
            
            // Update the cell display
            $cell.html(value);
            
            // ✅ Highlight computed fields that changed with a subtle animation
            if (computedFields.includes(field) && value !== '-') {
                $cell.addClass('bg-info text-white');
                setTimeout(() => {
                    $cell.removeClass('bg-info text-white');
                }, 1500);
            }
        });
    }

    // ✅ NEW HELPER FUNCTION: Format ISO date string to YYYY-MM-DD
    function formatDateFromISO(dateString) {
        if (!dateString || dateString === '-') return '-';
        
        try {
            // Handle ISO format: "2026-02-10T16:00:00.000000Z"
            // Handle already formatted: "2026-02-10"
            
            if (dateString.includes('T')) {
                // ISO format - extract just the date part
                return dateString.split('T')[0];
            } else {
                // Already in YYYY-MM-DD format
                return dateString;
            }
        } catch (e) {
            console.warn('Error formatting date:', dateString, e);
            return dateString; // Return original if parsing fails
        }
    }

    // Function to cancel edit
    function cancelEdit($cell) {
        restoreCellDisplay($cell, originalValue, $cell.data('type'));
        currentEditingCell = null;
    }

    // Function to restore cell display
    function restoreCellDisplay($cell, value, fieldType) {
        let displayValue = value || '-';
        
        // Format based on type
        if (fieldType === 'boolean') {
            displayValue = value === '1' || value === 1 || value === true || value === 'Yes' ? 'Yes' : 'No';
        } else if (fieldType === 'date' && value) {
            // Keep date format as is (YYYY-MM-DD)
            displayValue = value;
        } else if ($cell.data('field')?.includes('amount') || $cell.data('field')?.includes('monetary') || $cell.data('field')?.includes('penalty')) {
            // Format monetary values
            if (value && value !== '-') {
                const num = parseFloat(value);
                if (!isNaN(num)) {
                    displayValue = num.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                }
            }
        }
        
        // Handle long text with title attribute
        const originalTitle = $cell.attr('title');
        if (value && value.length > 30) {
            $cell.attr('title', value);
            displayValue = value.substring(0, 30) + '...';
        } else if (originalTitle) {
            $cell.attr('title', value);
        }
        
        $cell.html(displayValue);
    }

    // Toast notification function
    function showToast(title, message, type) {
        const bgColor = type === 'success' ? 'bg-success' : 'bg-danger';
        const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
        
        const toast = $(`
            <div class="toast-notification ${bgColor}" style="position: fixed; top: 80px; right: 20px; z-index: 9999; 
                min-width: 300px; padding: 15px; border-radius: 5px; color: white; box-shadow: 0 4px 6px rgba(0,0,0,0.3);">
                <div style="display: flex; align-items: center;">
                    <i class="fas ${icon}" style="font-size: 20px; margin-right: 10px;"></i>
                    <div>
                        <strong style="display: block; margin-bottom: 5px;">${title}</strong>
                        <span>${message}</span>
                    </div>
                </div>
            </div>
        `);
        
        $('body').append(toast);
        
        // Fade in
        toast.fadeIn(300);
        
        // Auto remove after 3 seconds
        setTimeout(() => {
            toast.fadeOut(300, function() {
                $(this).remove();
            });
        }, 3000);
    }

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
                        { value: 'Appealed', text: 'Appealed' },
                        { value: 'Disposed', text: 'Disposed' }
                    ]
                },
                
                // Inspection Stage
                'date_of_inspection': { type: 'date' },
                'inspector_name': { type: 'text' },
                'inspector_authority_no': { type: 'text' },
                'date_of_nr': { type: 'date' },
                'lapse_20_day_period': { type: 'date', readonly: true }, // ✅ COMPUTED
                
                // Docketing Stage
                'pct_for_docketing': { type: 'date', readonly: true }, // ✅ COMPUTED
                'date_scheduled_docketed': { type: 'date' },
                'aging_docket': { type: 'number', readonly: true }, // ✅ COMPUTED
                'status_docket': { type: 'text', readonly: true }, // ✅ COMPUTED - Changed from select to text
                'hearing_officer_mis': { type: 'text' },
                
                // Hearing Process Stage
                'date_1st_mc_actual': { type: 'date' },
                'first_mc_pct': { type: 'number', readonly: true }, // ✅ COMPUTED
                'status_1st_mc': { type: 'text', readonly: true }, // ✅ COMPUTED - Changed from select to text
                'date_2nd_last_mc': { type: 'date' },
                'second_last_mc_pct': { type: 'number', readonly: true }, // ✅ COMPUTED
                'status_2nd_mc': { type: 'text', readonly: true }, // ✅ COMPUTED - Changed from select to text
                'case_folder_forwarded_to_ro': { type: 'date' }, // Changed from text to date
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
                'po_pct': { type: 'date', readonly: true }, // ✅ COMPUTED
                'aging_po_pct': { type: 'number', readonly: true }, // ✅ COMPUTED
                'status_po_pct': { type: 'text', readonly: true }, // ✅ COMPUTED - Changed from select to text
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
                'pct_96_days': { type: 'date', readonly: true }, // ✅ COMPUTED
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

    // Helper function to display history timeline - newest at top, oldest at bottom
    function displayHistory(historyData) {
        if (!historyData || historyData.length === 0) {
            $('#historyContent').html(`
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> No transfer history available yet.
                </div>
            `);
            return;
        }

        // ────────────────────────────────────────────────────────────────
        // Show newest events at the top (reverse chronological order)
        // This matches the Document Tracking history page behavior
        // Creation (oldest) will now appear at the bottom
        // ────────────────────────────────────────────────────────────────
        const reversedData = [...historyData].reverse();
        console.log('Original first (should be oldest):', historyData[0]?.role, historyData[0]?.notes);
        console.log('Reversed first (should be newest):', reversedData[0]?.role, reversedData[0]?.notes);

        let timelineHtml = '<div class="timeline" style="position: relative; padding-left: 30px;">';
        timelineHtml += '<div style="content: \'\'; position: absolute; left: 10px; top: 0; bottom: 0; width: 2px; background: #e3e6f0;"></div>';

        reversedData.forEach((item, index) => {
            const roleClass = item.role ? item.role.toLowerCase().replace(/\s+/g, '_') : '';
            const statusClass = item.status === 'Received' ? 'success' : 'warning';

            // ────────────────────────────────────────────────────────────────
            // IMPORTANT: Detect case creation entry to show cleaner layout
            // 
            // We identify the very first (creation) history item by checking:
            // 1. Same person transferred and received (creator = initial receiver)
            // 2. Actions happened almost instantly (< 10 seconds apart)
            // 3. Notes contain "case created by" phrase
            // 
            // This avoids showing fake-looking "Transferred By" on creation.
            // 
            // Long-term better solution: Add real 'is_initial: true' flag in 
            // backend when creation doesn't set transferred_by / transferred_at.
            // ────────────────────────────────────────────────────────────────
            const isLikelyCreation =
                item.transferred_by === item.received_by &&
                Math.abs(new Date(item.transferred_at) - new Date(item.received_at)) < 10000 &&
                (item.notes || '').toLowerCase().includes('case created by');

            let transferContent = '';

            if (isLikelyCreation) {
                // Clean layout for case creation (no "Transferred By", no "from ...")
                transferContent = `
                    <div class="row">
                        <div class="col-md-12">
                            <small class="text-muted d-block">Created & Initially Received By:</small>
                            <strong class="text-success">${item.received_by}</strong><br>
                            <small class="text-muted">${item.received_at}</small>
                        </div>
                    </div>
                `;
            } else {
                // Normal transfer layout
                transferContent = `
                    <div class="row">
                        <div class="col-md-6">
                            <small class="text-muted d-block">Transferred By:</small>
                            <strong>${item.transferred_by}</strong><br>
                            <small class="text-muted">${item.transferred_at}</small>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted d-block">Received By:</small>
                            <strong class="${item.received_by === 'Pending' || item.received_by === 'Awaiting Receipt' || item.received_by === 'Not Yet Received' ? 'text-warning' : 'text-success'}">
                                ${item.received_by === 'Pending' ? 'Awaiting Receipt' : item.received_by}
                            </strong><br>
                            <small class="text-muted">${item.received_at}</small>
                        </div>
                    </div>
                `;
            }

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
                                    ${!isLikelyCreation && item.from_role ? '<small class="text-muted ml-2">from ' + item.from_role + '</small>' : ''}
                                </div>
                                <div class="text-right">
                                    <small class="text-muted"><i class="fas fa-clock"></i> ${item.time_ago}</small>
                                </div>
                            </div>
                            
                            ${transferContent}
                            
                            ${item.notes ? '<hr class="my-2"><small class="text-muted"><i class="fas fa-sticky-note"></i> <strong>Notes:</strong> ' + item.notes + '</small>' : ''}
                        </div>
                    </div>
                </div>
            `;
        });

        timelineHtml += '</div>';
        $('#historyContent').html(timelineHtml);
    }

// UPDATED: Export button → Show modal first
document.getElementById('exportActiveCasesXlsx').addEventListener('click', function () {
    const table = $('#dataTable0').DataTable();
    
    // Count rows for display
    const filteredCount = table.rows({ search: 'applied' }).count();
    const allCount = table.rows().count();
    
    document.getElementById('filteredCount').textContent = filteredCount;
    document.getElementById('allCount').textContent = allCount;
    
    // Show modal
    $('#exportOptionsModal').modal('show');
});

// NEW: Confirm export from modal (pure client-side with year filter on created_at)
document.getElementById('confirmExportBtn').addEventListener('click', function () {
    const table = $('#dataTable0').DataTable();
    const scope = document.querySelector('input[name="exportScope"]:checked').value;
    const yearFilter = document.getElementById('exportYear').value;
    
    // Close modal
    $('#exportOptionsModal').modal('hide');
    
    // Get data based on scope
    let rowsData = scope === 'filtered' 
        ? table.rows({ search: 'applied' }).data().toArray()
        : table.rows().data().toArray();
    
    // YEAR FILTER on "Created At" (last column index = row.length - 1)
    if (yearFilter) {
        rowsData = rowsData.filter(row => {
            const createdAt = row[row.length - 1]; // Last column = Created At
            if (!createdAt || createdAt === '-') return false;
            return createdAt.toString().startsWith(yearFilter); // YYYY-MM-DD → check year
        });
    }
    
    if (rowsData.length === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'No data to export',
            text: yearFilter ? `No cases found for ${yearFilter}` : 'Table is empty or no rows match filters.',
            confirmButtonText: 'OK'
        });
        return;
    }
    
    // Get headers (skip Actions column)
    const headers = [];
    $('#dataTable0 thead th').slice(1).each(function() {
        headers.push($(this).text().trim());
    });
    
    // Build export data
    const exportData = [headers];
    rowsData.forEach(row => {
        const rowData = [];
        for (let i = 1; i < row.length; i++) { // Skip Actions (i=0)
            let cellValue = row[i];
            if (typeof cellValue === 'string') {
                cellValue = cellValue.replace(/<[^>]*>/g, '').replace(/\s+/g, ' ').trim();
            }
            if (cellValue === '-' || cellValue === '' || cellValue == null) {
                cellValue = '';
            } else if (!isNaN(cellValue) && cellValue !== '') {
                cellValue = Number(cellValue);
            }
            rowData.push(cellValue);
        }
        exportData.push(rowData);
    });
    
    // SheetJS magic (same as before)
    const ws = XLSX.utils.aoa_to_sheet(exportData);
    
    // Auto-size columns
    const colWidths = headers.map((header, idx) => {
        let maxLen = header.length;
        exportData.forEach(row => {
            const val = row[idx + 1];
            if (val && val.toString().length > maxLen) maxLen = val.toString().length;
        });
        return { wch: Math.min(80, maxLen + 4) };
    });
    ws['!cols'] = colWidths;
    
    const wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, "Active Cases");
    
    const today = new Date().toISOString().slice(0, 10);
    const yearStr = yearFilter ? `_${yearFilter}` : '';
    const scopeStr = scope === 'filtered' ? '_filtered' : '_all';
    const filename = `Active_Cases${scopeStr}${yearStr}_${today}.xlsx`;
    
    XLSX.writeFile(wb, filename);
    
    // Success feedback
    Swal.fire({
        icon: 'success',
        title: 'Exported!',
        text: `${exportData.length - 1} rows saved to ${filename}`,
        timer: 2500,
        showConfirmButton: false
    });
});

</script>
@endpush