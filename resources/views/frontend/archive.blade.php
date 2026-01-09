@extends('frontend.layouts.app')

@section('content')

<style>
    /* Custom styles */
    .container {
        width: 100%;
        max-width: 1200px;
        margin-left: auto;
        margin-right: auto;
    }
    .p-4 {
        padding: 1rem;
    }
    .bg-gray-100 {
        background-color: #f3f4f6;
    }
    .text-2xl {
        font-size: 1.5rem;
        line-height: 2rem;
    }
    .font-bold {
        font-weight: 700;
    }
    .mb-4 {
        margin-bottom: 1rem;
    }
    .flex {
        display: flex;
    }
    .gap-4 {
        gap: 1rem;
    }
    .border {
        border: 1px solid #d1d5db;
    }
    .rounded {
        border-radius: 0.25rem;
    }
    .w-full {
        width: 100%;
    }
    .max-w-md {
        max-width: 28rem;
    }
    .bg-blue-600 {
        background-color: #2563eb;
    }
    .text-white {
        color: #ffffff;
    }
    .px-4 {
        padding-left: 1rem;
        padding-right: 1rem;
    }
    .py-2 {
        padding-top: 0.5rem;
        padding-bottom: 0.5rem;
    }
    .hover-bg-blue-700:hover {
        background-color: #1d4ed8;
    }
    .bg-white {
        background-color: #ffffff;
    }
    .shadow {
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1), 0 1px 2px rgba(0, 0, 0, 0.06);
    }
    .space-y-4 > :not(:last-child) {
        margin-bottom: 1rem;
    }
    .text-green-600 {
        color: #16a34a;
    }
    .text-blue-600 {
        color: #2563eb;
    }
    .hover-text-blue-800:hover {
        color: #1e40af;
    }
    .bg-gray-50 {
        background-color: #f9fafb;
    }
    .justify-between {
        justify-content: space-between;
    }
    .items-center {
        align-items: center;
    }
    .cursor-pointer {
        cursor: pointer;
    }

    /* Accordion styles */
    .accordion-header:hover {
        background-color: #e5e7eb;
    }
    .accordion-content {
        display: none;
    }
    .accordion-content.active {
        display: block !important;
        min-height: 50px;
        overflow: visible;
    }
    
    /* Tab styles */
    .tab {
        cursor: pointer;
        padding: 0.5rem 1rem;
        border-bottom: 2px solid transparent;
        font-size: 0.875rem;
        transition: all 0.2s ease;
    }
    .tab:hover {
        background-color: #f3f4f6;
    }
    .tab.active {
        border-bottom: 2px solid #1e40af;
        color: #1e40af;
        font-weight: bold;
        background-color: #eff6ff;
    }
    .tab-content {
        display: none;
        padding: 1rem 0;
    }
    .tab-content.active {
        display: block;
    }
    .border-b {
        border-bottom: 1px solid #e5e7eb;
    }
    .detail-row {
        padding: 0.75rem 0;
        border-bottom: 1px solid #f3f4f6;
        display: grid;
        grid-template-columns: 250px 1fr;
        gap: 1rem;
    }
    .detail-row:last-child {
        border-bottom: none;
    }
    .detail-label {
        font-weight: 600;
        color: #374151;
    }
    .detail-value {
        color: #6b7280;
    }

    /* Document tracking timeline styles */
    .doc-timeline {
        position: relative;
        padding-left: 30px;
        margin-top: 1rem;
    }
    .doc-timeline::before {
        content: '';
        position: absolute;
        left: 10px;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #e3e6f0;
    }
    .doc-timeline-item {
        position: relative;
        margin-bottom: 1.5rem;
    }
    .doc-timeline-item::before {
        content: '';
        position: absolute;
        left: -24px;
        top: 5px;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: #4e73df;
        border: 2px solid white;
        box-shadow: 0 0 0 2px #e3e6f0;
    }
    .doc-card {
        background: white;
        border: 1px solid #e3e6f0;
        border-radius: 0.25rem;
        padding: 0.75rem;
        margin-bottom: 0;
    }
    .doc-badge {
        display: inline-block;
        padding: 0.35rem 0.75rem;
        border-radius: 12px;
        font-weight: 600;
        font-size: 0.75rem;
        margin-right: 0.5rem;
    }
    .doc-badge-admin { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
    .doc-badge-malsu { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; }
    .doc-badge-case_management { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white; }
    .doc-badge-province { background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); color: white; }
    .doc-badge-records { background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); color: white; }
    .text-muted {
        color: #6c757d;
        font-size: 0.875rem;
    }
    .text-success {
        color: #28a745;
    }
    .text-warning {
        color: #ffc107;
    }
    .loading-spinner {
        text-align: center;
        padding: 2rem;
    }
    .spinner-border {
        display: inline-block;
        width: 2rem;
        height: 2rem;
        vertical-align: text-bottom;
        border: 0.25em solid currentColor;
        border-right-color: transparent;
        border-radius: 50%;
        animation: spinner-border .75s linear infinite;
    }
    @keyframes spinner-border {
        to { transform: rotate(360deg); }
    }
    .no-history-msg {
        text-align: center;
        padding: 2rem;
        color: #6c757d;
    }
</style>

<div class="container p-4 bg-gray-100">
    <h1 class="text-2xl font-bold mb-4">DOLE Archived Cases</h1>
    
    <!-- Search and Filter -->
    <div class="mb-4 flex gap-4">
        <input type="text" id="searchInput" placeholder="Search by Case No. or Establishment Name" 
               class="p-2 border rounded w-full max-w-md">
        <button class="bg-blue-600 text-white px-4 py-2 rounded hover-bg-blue-700" onclick="exportSelected()">
            Export Selected
        </button>
    </div>

    <!-- Accordion List -->
    <div id="caseList" class="space-y-4">
        @if($archivedCases->count() > 0)
            @foreach($archivedCases as $case)
                <div class="bg-white shadow rounded" data-case-id="{{ $case->id }}">
                    <div class="accordion-header p-4 flex justify-between items-center cursor-pointer">
                        <div>
                            <span class="font-bold">Inspection ID:</span> {{ $case->inspection_id }}<br>
                            <span class="font-bold">Case No.:</span> {{ $case->case_no ?? 'N/A' }}<br>
                            <span class="font-bold">Establishment:</span> {{ $case->establishment_name }}<br>
                            <span class="font-bold">Date Archived:</span> {{ $case->updated_at->format('Y-m-d') }}<br>
                            <span class="font-bold">Status:</span> <span class="text-green-600">{{ $case->overall_status }}</span>
                        </div>
                        <div>
                            <button class="text-blue-600 hover-text-blue-800">View Details</button>
                        </div>
                    </div>
                    <div class="accordion-content p-4 bg-gray-50">
                        <!-- Main Tab Navigation -->
                        <div class="flex border-b mb-4" style="flex-wrap: wrap;">
                            <div class="tab active" data-tab="overview-{{ $case->id }}">📋 Overview</div>
                            <div class="tab" data-tab="inspection-{{ $case->id }}">🔍 Inspection</div>
                            <div class="tab" data-tab="docketing-{{ $case->id }}">📝 Docketing</div>
                            <div class="tab" data-tab="hearing-{{ $case->id }}">⚖️ Hearing</div>
                            <div class="tab" data-tab="review-{{ $case->id }}">✍️ Review & Drafting</div>
                            <div class="tab" data-tab="orders-{{ $case->id }}">📑 Orders</div>
                            <div class="tab" data-tab="compliance-{{ $case->id }}">💰 Compliance</div>
                            <div class="tab" data-tab="appeals-{{ $case->id }}">📢 Appeals</div>
                            <div class="tab" data-tab="additional-{{ $case->id }}">📌 Additional</div>
                            <div class="tab" data-tab="doc-history-{{ $case->id }}">📍 Document History</div>
                        </div>
                        
                        <!-- Overview Tab -->
                        <div id="overview-{{ $case->id }}" class="tab-content active">
                            <h3 class="font-bold mb-3" style="font-size: 1.25rem; color: #1f2937;">Core Information</h3>
                            
                            <div class="detail-row">
                                <span class="detail-label">No.:</span>
                                <span class="detail-value">{{ $case->no ?? '-' }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Inspection ID:</span>
                                <span class="detail-value">{{ $case->inspection_id ?? '-' }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Case No.:</span>
                                <span class="detail-value">{{ $case->case_no ?? '-' }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Establishment Name:</span>
                                <span class="detail-value">{{ $case->establishment_name ?? '-' }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Establishment Address:</span>
                                <span class="detail-value">{{ $case->establishment_address ?? '-' }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Mode:</span>
                                <span class="detail-value">{{ $case->mode ?? '-' }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">PO Office:</span>
                                <span class="detail-value">{{ $case->po_office ?? '-' }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Current Stage:</span>
                                <span class="detail-value">{{ $case->current_stage ?? '-' }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Overall Status:</span>
                                <span class="detail-value text-green-600" style="font-weight: 600;">{{ $case->overall_status ?? '-' }}</span>
                            </div>
                        </div>

                        <!-- Inspection Tab -->
                        <div id="inspection-{{ $case->id }}" class="tab-content">
                            <h3 class="font-bold mb-3" style="font-size: 1.25rem; color: #1f2937;">Inspection Information</h3>
                            
                            <div class="detail-row">
                                <span class="detail-label">Date of Inspection:</span>
                                <span class="detail-value">{{ $case->date_of_inspection ? \Carbon\Carbon::parse($case->date_of_inspection)->format('Y-m-d') : '-' }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Inspector Name:</span>
                                <span class="detail-value">{{ $case->inspector_name ?? '-' }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Inspector Authority No.:</span>
                                <span class="detail-value">{{ $case->inspector_authority_no ?? '-' }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Date of NR:</span>
                                <span class="detail-value">{{ $case->date_of_nr ? \Carbon\Carbon::parse($case->date_of_nr)->format('Y-m-d') : '-' }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Lapse 20 Day Period:</span>
                                <span class="detail-value">{{ $case->lapse_20_day_period ?? '-' }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">TWG ALI:</span>
                                <span class="detail-value">{{ $case->twg_ali ?? '-' }}</span>
                            </div>
                        </div>

                        <!-- Docketing Tab -->
                        <div id="docketing-{{ $case->id }}" class="tab-content">
                            <h3 class="font-bold mb-3" style="font-size: 1.25rem; color: #1f2937;">Docketing Information</h3>
                            
                            <div class="detail-row">
                                <span class="detail-label">PCT for Docketing:</span>
                                <span class="detail-value">{{ $case->pct_for_docketing ?? '-' }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Date Scheduled/Docketed:</span>
                                <span class="detail-value">{{ $case->date_scheduled_docketed ? \Carbon\Carbon::parse($case->date_scheduled_docketed)->format('Y-m-d') : '-' }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Aging Docket:</span>
                                <span class="detail-value">{{ $case->aging_docket ?? '-' }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Status Docket:</span>
                                <span class="detail-value">{{ $case->status_docket ?? '-' }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Hearing Officer (MIS):</span>
                                <span class="detail-value">{{ $case->hearing_officer_mis ?? '-' }}</span>
                            </div>
                        </div>

                        <!-- Hearing Tab -->
                        <div id="hearing-{{ $case->id }}" class="tab-content">
                            <h3 class="font-bold mb-3" style="font-size: 1.25rem; color: #1f2937;">Hearing Process Information</h3>
                            
                            <div class="detail-row">
                                <span class="detail-label">Date 1st MC (Actual):</span>
                                <span class="detail-value">{{ $case->date_1st_mc_actual ? \Carbon\Carbon::parse($case->date_1st_mc_actual)->format('Y-m-d') : '-' }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">First MC PCT:</span>
                                <span class="detail-value">{{ $case->first_mc_pct ?? '-' }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Status 1st MC:</span>
                                <span class="detail-value">{{ $case->status_1st_mc ?? '-' }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Date 2nd/Last MC:</span>
                                <span class="detail-value">{{ $case->date_2nd_last_mc ? \Carbon\Carbon::parse($case->date_2nd_last_mc)->format('Y-m-d') : '-' }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Second/Last MC PCT:</span>
                                <span class="detail-value">{{ $case->second_last_mc_pct ?? '-' }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Status 2nd MC:</span>
                                <span class="detail-value">{{ $case->status_2nd_mc ?? '-' }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Case Folder Forwarded to RO:</span>
                                <span class="detail-value">{{ $case->case_folder_forwarded_to_ro ?? '-' }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Draft Order from PO Type:</span>
                                <span class="detail-value">{{ $case->draft_order_from_po_type ?? '-' }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Applicable Draft Order:</span>
                                <span class="detail-value">{{ $case->applicable_draft_order ?? '-' }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Complete Case Folder:</span>
                                <span class="detail-value">{{ $case->complete_case_folder ?? '-' }}</span>
                            </div>
                        </div>

                        <!-- Review & Drafting Tab -->
                        <div id="review-{{ $case->id }}" class="tab-content">
                            <h3 class="font-bold mb-3" style="font-size: 1.25rem; color: #1f2937;">Review & Drafting Information</h3>
                            
                            <div class="detail-row">
                                <span class="detail-label">PO PCT:</span>
                                <span class="detail-value">{{ $case->po_pct ?? '-' }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Aging PO PCT:</span>
                                <span class="detail-value">{{ $case->aging_po_pct ?? '-' }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Status PO PCT:</span>
                                <span class="detail-value">{{ $case->status_po_pct ?? '-' }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Date Received from PO:</span>
                                <span class="detail-value">{{ $case->date_received_from_po ? \Carbon\Carbon::parse($case->date_received_from_po)->format('Y-m-d') : '-' }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Reviewer/Drafter:</span>
                                <span class="detail-value">{{ $case->reviewer_drafter ?? '-' }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Date Received by Reviewer:</span>
                                <span class="detail-value">{{ $case->date_received_by_reviewer ? \Carbon\Carbon::parse($case->date_received_by_reviewer)->format('Y-m-d') : '-' }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Date Returned from Drafter:</span>
                                <span class="detail-value">{{ $case->date_returned_from_drafter ? \Carbon\Carbon::parse($case->date_returned_from_drafter)->format('Y-m-d') : '-' }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Aging 10 Days TSSD:</span>
                                <span class="detail-value">{{ $case->aging_10_days_tssd ?? '-' }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Status Reviewer/Drafter:</span>
                                <span class="detail-value">{{ $case->status_reviewer_drafter ?? '-' }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Draft Order TSSD Reviewer:</span>
                                <span class="detail-value">{{ $case->draft_order_tssd_reviewer ?? '-' }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Final Review Date Received:</span>
                                <span class="detail-value">{{ $case->final_review_date_received ? \Carbon\Carbon::parse($case->final_review_date_received)->format('Y-m-d') : '-' }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Date Received Drafter Finalization:</span>
                                <span class="detail-value">{{ $case->date_received_drafter_finalization ? \Carbon\Carbon::parse($case->date_received_drafter_finalization)->format('Y-m-d') : '-' }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Date Returned Case Mgmt Signature:</span>
                                <span class="detail-value">{{ $case->date_returned_case_mgmt_signature ? \Carbon\Carbon::parse($case->date_returned_case_mgmt_signature)->format('Y-m-d') : '-' }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Aging 2 Days Finalization:</span>
                                <span class="detail-value">{{ $case->aging_2_days_finalization ?? '-' }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Status Finalization:</span>
                                <span class="detail-value">{{ $case->status_finalization ?? '-' }}</span>
                            </div>
                        </div>

                        <!-- Orders Tab -->
                        <div id="orders-{{ $case->id }}" class="tab-content">
                            <h3 class="font-bold mb-3" style="font-size: 1.25rem; color: #1f2937;">Orders & Disposition Information</h3>
                            
                            <div class="detail-row">
                                <span class="detail-label">PCT 96 Days:</span>
                                <span class="detail-value">{{ $case->pct_96_days ?? '-' }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Date Signed (MIS):</span>
                                <span class="detail-value">{{ $case->date_signed_mis ? \Carbon\Carbon::parse($case->date_signed_mis)->format('Y-m-d') : '-' }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Status PCT:</span>
                                <span class="detail-value">{{ $case->status_pct ?? '-' }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Reference Date PCT:</span>
                                <span class="detail-value">{{ $case->reference_date_pct ? \Carbon\Carbon::parse($case->reference_date_pct)->format('Y-m-d') : '-' }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Aging PCT:</span>
                                <span class="detail-value">{{ $case->aging_pct ?? '-' }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Disposition (MIS):</span>
                                <span class="detail-value">{{ $case->disposition_mis ?? '-' }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Disposition (Actual):</span>
                                <span class="detail-value">{{ $case->disposition_actual ?? '-' }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Findings to Comply:</span>
                                <span class="detail-value">{{ $case->findings_to_comply ?? '-' }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Date of Order (Actual):</span>
                                <span class="detail-value">{{ $case->date_of_order_actual ? \Carbon\Carbon::parse($case->date_of_order_actual)->format('Y-m-d') : '-' }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Released Date (Actual):</span>
                                <span class="detail-value">{{ $case->released_date_actual ? \Carbon\Carbon::parse($case->released_date_actual)->format('Y-m-d') : '-' }}</span>
                            </div>
                        </div>

                        <!-- Compliance Tab -->
                        <div id="compliance-{{ $case->id }}" class="tab-content">
                            <h3 class="font-bold mb-3" style="font-size: 1.25rem; color: #1f2937;">Compliance & Awards Information</h3>
                            
                            <div class="detail-row">
                                <span class="detail-label">Compliance Order Monetary Award:</span>
                                <span class="detail-value">{{ $case->compliance_order_monetary_award ? '₱' . number_format($case->compliance_order_monetary_award, 2) : '-' }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">OSH Penalty:</span>
                                <span class="detail-value">{{ $case->osh_penalty ? '₱' . number_format($case->osh_penalty, 2) : '-' }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Affected Workers (Male):</span>
                                <span class="detail-value">{{ $case->affected_male ?? '-' }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Affected Workers (Female):</span>
                                <span class="detail-value">{{ $case->affected_female ?? '-' }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">First Order Dismissal CNPC:</span>
                                <span class="detail-value">{{ $case->first_order_dismissal_cnpc ? 'Yes' : 'No' }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Tavable Less Than 10 Workers:</span>
                                <span class="detail-value">{{ $case->tavable_less_than_10_workers ? 'Yes' : 'No' }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Scanned Order First:</span>
                                <span class="detail-value">{{ $case->scanned_order_first ?? '-' }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">With Deposited Monetary Claims:</span>
                                <span class="detail-value">{{ $case->with_deposited_monetary_claims ? 'Yes' : 'No' }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Amount Deposited:</span>
                                <span class="detail-value">{{ $case->amount_deposited ? '₱' . number_format($case->amount_deposited, 2) : '-' }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">With Order Payment Notice:</span>
                                <span class="detail-value">{{ $case->with_order_payment_notice ? 'Yes' : 'No' }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Status All Employees Received:</span>
                                <span class="detail-value">{{ $case->status_all_employees_received ?? '-' }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Status Case After First Order:</span>
                                <span class="detail-value">{{ $case->status_case_after_first_order ?? '-' }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Date Notice Finality Dismissed:</span>
                                <span class="detail-value">{{ $case->date_notice_finality_dismissed ? \Carbon\Carbon::parse($case->date_notice_finality_dismissed)->format('Y-m-d') : '-' }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Released Date Notice Finality:</span>
                                <span class="detail-value">{{ $case->released_date_notice_finality ? \Carbon\Carbon::parse($case->released_date_notice_finality)->format('Y-m-d') : '-' }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Scanned Notice Finality:</span>
                                <span class="detail-value">{{ $case->scanned_notice_finality ?? '-' }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Updated/Ticked in MIS:</span>
                                <span class="detail-value">{{ $case->updated_ticked_in_mis ? 'Yes' : 'No' }}</span>
                            </div>
                        </div>

                        <!-- Appeals Tab -->
                        <div id="appeals-{{ $case->id }}" class="tab-content">
                            <h3 class="font-bold mb-3" style="font-size: 1.25rem; color: #1f2937;">Appeals & Resolution Information</h3>
                            
                            <h4 class="font-bold mb-2" style="font-size: 1.1rem; color: #374151; margin-top: 1.5rem;">2nd Order (CNPC)</h4>
                            <div class="detail-row">
                                <span class="detail-label">Second Order Drafter:</span>
                                <span class="detail-value">{{ $case->second_order_drafter ?? '-' }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Date Received by Drafter CT CNPC:</span>
                                <span class="detail-value">{{ $case->date_received_by_drafter_ct_cnpc ? \Carbon\Carbon::parse($case->date_received_by_drafter_ct_cnpc)->format('Y-m-d') : '-' }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Date Returned Case Mgmt CT CNPC:</span>
                                <span class="detail-value">{{ $case->date_returned_case_mgmt_ct_cnpc ? \Carbon\Carbon::parse($case->date_returned_case_mgmt_ct_cnpc)->format('Y-m-d') : '-' }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Review CT CNPC:</span>
                                <span class="detail-value">{{ $case->review_ct_cnpc ?? '-' }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Date Received Drafter Finalization (2nd):</span>
                                <span class="detail-value">{{ $case->date_received_drafter_finalization_2nd ? \Carbon\Carbon::parse($case->date_received_drafter_finalization_2nd)->format('Y-m-d') : '-' }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Date Returned Case Mgmt Signature (2nd):</span>
                                <span class="detail-value">{{ $case->date_returned_case_mgmt_signature_2nd ? \Carbon\Carbon::parse($case->date_returned_case_mgmt_signature_2nd)->format('Y-m-d') : '-' }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Date Order (2nd CNPC):</span>
                                <span class="detail-value">{{ $case->date_order_2nd_cnpc ? \Carbon\Carbon::parse($case->date_order_2nd_cnpc)->format('Y-m-d') : '-' }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Released Date (2nd CNPC):</span>
                                <span class="detail-value">{{ $case->released_date_2nd_cnpc ? \Carbon\Carbon::parse($case->released_date_2nd_cnpc)->format('Y-m-d') : '-' }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Scanned Order (2nd CNPC):</span>
                                <span class="detail-value">{{ $case->scanned_order_2nd_cnpc ?? '-' }}</span>
                            </div>

                            <h4 class="font-bold mb-2" style="font-size: 1.1rem; color: #374151; margin-top: 1.5rem;">MALSU Process</h4>
                            <div class="detail-row">
                                <span class="detail-label">Date Forwarded MALSU:</span>
                                <span class="detail-value">{{ $case->date_forwarded_malsu ? \Carbon\Carbon::parse($case->date_forwarded_malsu)->format('Y-m-d') : '-' }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Scanned Indorsement MALSU:</span>
                                <span class="detail-value">{{ $case->scanned_indorsement_malsu ?? '-' }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Motion Reconsideration Date:</span>
                                <span class="detail-value">{{ $case->motion_reconsideration_date ? \Carbon\Carbon::parse($case->motion_reconsideration_date)->format('Y-m-d') : '-' }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Date Received MALSU:</span>
                                <span class="detail-value">{{ $case->date_received_malsu ? \Carbon\Carbon::parse($case->date_received_malsu)->format('Y-m-d') : '-' }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Date Resolution MR:</span>
                                <span class="detail-value">{{ $case->date_resolution_mr ? \Carbon\Carbon::parse($case->date_resolution_mr)->format('Y-m-d') : '-' }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Released Date Resolution MR:</span>
                                <span class="detail-value">{{ $case->released_date_resolution_mr ? \Carbon\Carbon::parse($case->released_date_resolution_mr)->format('Y-m-d') : '-' }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Scanned Resolution MR:</span>
                                <span class="detail-value">{{ $case->scanned_resolution_mr ?? '-' }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Date Appeal Received Records:</span>
                                <span class="detail-value">{{ $case->date_appeal_received_records ? \Carbon\Carbon::parse($case->date_appeal_received_records)->format('Y-m-d') : '-' }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Date Indorsed Office Secretary:</span>
                                <span class="detail-value">{{ $case->date_indorsed_office_secretary ? \Carbon\Carbon::parse($case->date_indorsed_office_secretary)->format('Y-m-d') : '-' }}</span>
                            </div>
                        </div>

                        <!-- Additional Tab -->
                        <div id="additional-{{ $case->id }}" class="tab-content">
                            <h3 class="font-bold mb-3" style="font-size: 1.25rem; color: #1f2937;">Additional Information</h3>
                            
                            <div class="detail-row">
                                <span class="detail-label">Logbook Page Number:</span>
                                <span class="detail-value">{{ $case->logbook_page_number ?? '-' }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Remarks/Notes:</span>
                                <span class="detail-value">{{ $case->remarks_notes ?? '-' }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Created At:</span>
                                <span class="detail-value">{{ $case->created_at ? \Carbon\Carbon::parse($case->created_at)->format('Y-m-d H:i:s') : '-' }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Last Updated:</span>
                                <span class="detail-value">{{ $case->updated_at ? \Carbon\Carbon::parse($case->updated_at)->format('Y-m-d H:i:s') : '-' }}</span>
                            </div>
                        </div>

                        <!-- Document History Tab -->
                        <div id="doc-history-{{ $case->id }}" class="tab-content">
                            <h3 class="font-bold mb-3">📍 Document Tracking History</h3>
                            <p class="text-muted mb-3">Complete journey of this case's physical documents through departments</p>
                            
                            <div class="doc-history-container" data-case-id="{{ $case->id }}">
                                <div class="loading-spinner">
                                    <div class="spinner-border text-blue-600"></div>
                                    <p class="text-muted mt-2">Loading document history...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        @else
            <div class="bg-white shadow rounded p-4">
                <p class="text-center">No archived cases found.</p>
            </div>
        @endif
    </div>
</div>

<script>
    // Accordion Toggle
    document.querySelectorAll('.accordion-header').forEach(header => {
        header.addEventListener('click', (e) => {
            if (e.target.closest('button')) {
                const content = header.nextElementSibling;
                content.classList.toggle('active');
            }
        });
    });

    // Tab Switching with lazy loading for Document History
    document.querySelectorAll('.tab').forEach(tab => {
        tab.addEventListener('click', () => {
            const accordion = tab.closest('.accordion-content');
            accordion.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
            accordion.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));

            tab.classList.add('active');
            const tabContentId = tab.getAttribute('data-tab');
            const tabContent = accordion.querySelector(`#${tabContentId}`);
            tabContent.classList.add('active');

            // If Document History tab is clicked, load the history
            if (tabContentId.startsWith('doc-history-')) {
                const caseId = tabContentId.replace('doc-history-', '');
                const historyContainer = tabContent.querySelector('.doc-history-container');
                
                // Only load if not already loaded
                if (historyContainer && historyContainer.querySelector('.loading-spinner')) {
                    loadDocumentHistory(caseId, historyContainer);
                }
            }
        });
    });

    // Function to load document history via AJAX
    function loadDocumentHistory(caseId, container) {
        fetch(`/case/${caseId}/document-history`, {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (data.has_tracking && data.history && data.history.length > 0) {
                    displayDocumentHistory(data.history, container);
                } else {
                    container.innerHTML = `
                        <div class="no-history-msg">
                            <svg style="width: 48px; height: 48px; color: #cbd5e0; margin: 0 auto 1rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <p style="color: #718096; margin-bottom: 0.5rem;"><strong>No Document Tracking History</strong></p>
                            <p style="color: #a0aec0; font-size: 0.875rem;">This case was completed before document tracking was implemented.</p>
                        </div>
                    `;
                }
            } else {
                container.innerHTML = `
                    <div class="no-history-msg">
                        <p style="color: #e53e3e;">Failed to load document history.</p>
                        <button onclick="location.reload()" style="margin-top: 1rem; padding: 0.5rem 1rem; background: #4299e1; color: white; border: none; border-radius: 0.25rem; cursor: pointer;">
                            Retry
                        </button>
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error loading document history:', error);
            container.innerHTML = `
                <div class="no-history-msg">
                    <p style="color: #e53e3e;">Error loading document history.</p>
                    <p style="color: #a0aec0; font-size: 0.875rem;">${error.message}</p>
                </div>
            `;
        });
    }

    // Function to display document history timeline
    function displayDocumentHistory(history, container) {
        if (!history || history.length === 0) {
            container.innerHTML = '<div class="no-history-msg">No transfer history available.</div>';
            return;
        }

        const roleClassMap = {
            'Admin': 'admin',
            'MALSU': 'malsu',
            'Case Management': 'case_management',
            'Province': 'province',
            'Records': 'records'
        };

        let timelineHtml = '<div class="doc-timeline">';
        
        history.forEach((item, index) => {
            const roleClass = roleClassMap[item.role] || 'admin';
            const isReceived = item.received_by !== 'Pending' && item.received_by !== 'Not Received';
            const statusClass = isReceived ? 'text-success' : 'text-warning';
            
            timelineHtml += `
                <div class="doc-timeline-item">
                    <div class="doc-card">
                        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 0.75rem;">
                            <div>
                                <span class="doc-badge doc-badge-${roleClass}">${item.role}</span>
                                ${item.from_role ? `<span class="text-muted" style="font-size: 0.75rem;">from ${item.from_role}</span>` : ''}
                            </div>
                            <div style="text-align: right;">
                                <span class="text-muted" style="font-size: 0.75rem;">
                                    <svg style="width: 14px; height: 14px; display: inline-block; vertical-align: middle;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    ${item.time_ago}
                                </span>
                            </div>
                        </div>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 0.5rem;">
                            <div>
                                <small class="text-muted" style="display: block; margin-bottom: 0.25rem;">Transferred By:</small>
                                <strong style="font-size: 0.875rem;">${item.transferred_by}</strong>
                                <br>
                                <small class="text-muted">${item.transferred_at}</small>
                            </div>
                            <div>
                                <small class="text-muted" style="display: block; margin-bottom: 0.25rem;">Received By:</small>
                                <strong class="${statusClass}" style="font-size: 0.875rem;">${item.received_by}</strong>
                                <br>
                                <small class="text-muted">${item.received_at}</small>
                            </div>
                        </div>
                        
                        ${item.notes ? `
                            <div style="margin-top: 0.75rem; padding-top: 0.75rem; border-top: 1px solid #e5e7eb;">
                                <small class="text-muted">
                                    <svg style="width: 14px; height: 14px; display: inline-block; vertical-align: middle;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path>
                                    </svg>
                                    <strong>Notes:</strong> ${item.notes}
                                </small>
                            </div>
                        ` : ''}
                    </div>
                </div>
            `;
        });
        
        timelineHtml += '</div>';
        
        // Add summary at the top
        const totalTransfers = history.length;
        const completedTransfers = history.filter(h => h.received_by !== 'Pending' && h.received_by !== 'Not Received').length;
        
        const summaryHtml = `
            <div style="background: #f7fafc; border: 1px solid #e2e8f0; border-radius: 0.25rem; padding: 1rem; margin-bottom: 1.5rem;">
                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; text-align: center;">
                    <div>
                        <div style="font-size: 1.5rem; font-weight: bold; color: #2b6cb0;">${totalTransfers}</div>
                        <div style="font-size: 0.75rem; color: #718096; text-transform: uppercase;">Total Transfers</div>
                    </div>
                    <div>
                        <div style="font-size: 1.5rem; font-weight: bold; color: #2f855a;">${completedTransfers}</div>
                        <div style="font-size: 0.75rem; color: #718096; text-transform: uppercase;">Completed</div>
                    </div>
                    <div>
                        <div style="font-size: 1.5rem; font-weight: bold; color: #c05621;">${totalTransfers - completedTransfers}</div>
                        <div style="font-size: 0.75rem; color: #718096; text-transform: uppercase;">Pending</div>
                    </div>
                </div>
            </div>
        `;
        
        container.innerHTML = summaryHtml + timelineHtml;
    }

    // Basic Search Functionality
    document.getElementById('searchInput').addEventListener('input', (e) => {
        const searchTerm = e.target.value.toLowerCase();
        const cases = document.querySelectorAll('#caseList > div');
        cases.forEach(caseItem => {
            const text = caseItem.textContent.toLowerCase();
            caseItem.style.display = text.includes(searchTerm) ? '' : 'none';
        });
    });

    function exportSelected() {
        alert('Export functionality to be implemented');
    }
</script>

@stop