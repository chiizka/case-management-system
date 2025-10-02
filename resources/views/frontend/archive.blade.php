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
    }
    .tab.active {
        border-bottom: 2px solid #1e40af;
        color: #1e40af;
        font-weight: bold;
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
        padding: 0.5rem 0;
        border-bottom: 1px solid #f3f4f6;
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
                        <!-- Tab Navigation -->
                        <div class="flex border-b mb-4" style="flex-wrap: wrap;">
                            <div class="tab active" data-tab="stage1-{{ $case->id }}">1: Inspection</div>
                            <div class="tab" data-tab="stage2-{{ $case->id }}">2: Docketing</div>
                            <div class="tab" data-tab="stage3-{{ $case->id }}">3: Hearing</div>
                            <div class="tab" data-tab="stage4-{{ $case->id }}">4: Review & Drafting</div>
                            <div class="tab" data-tab="stage5-{{ $case->id }}">5: Orders & Disposition</div>
                            <div class="tab" data-tab="stage6-{{ $case->id }}">6: Compliance & Awards</div>
                            <div class="tab" data-tab="stage7-{{ $case->id }}">7: Appeals & Resolution</div>
                        </div>
                        
                        <!-- Tab Content -->
                        @php
                            $inspection = App\Models\Inspection::where('case_id', $case->id)->first();
                            $docketing = App\Models\Docketing::where('case_id', $case->id)->first();
                            $hearing = App\Models\HearingProcess::where('case_id', $case->id)->first();
                            $reviewAndDrafting = App\Models\ReviewAndDrafting::where('case_id', $case->id)->first();
                            $ordersAndDisposition = App\Models\OrderAndDisposition::where('case_id', $case->id)->first();
                            $complianceAndAwards = App\Models\ComplianceAndAward::where('case_id', $case->id)->first();
                            $appealsAndResolution = App\Models\AppealsAndResolution::where('case_id', $case->id)->first();
                        @endphp
                        
                        <!-- Stage 1: Inspection -->
                        <div id="stage1-{{ $case->id }}" class="tab-content active">
                            <h3 class="font-bold mb-3">Stage 1: Inspection Details</h3>
                            @if($inspection)
                                <div class="detail-row"><strong>PO Office:</strong> {{ $inspection->po_office ?? '-' }}</div>
                                <div class="detail-row"><strong>Inspector Name:</strong> {{ $inspection->inspector_name ?? '-' }}</div>
                                <div class="detail-row"><strong>Inspector Authority No:</strong> {{ $inspection->inspector_authority_no ?? '-' }}</div>
                                <div class="detail-row"><strong>Date of Inspection:</strong> {{ $inspection->date_of_inspection ? \Carbon\Carbon::parse($inspection->date_of_inspection)->format('Y-m-d') : '-' }}</div>
                                <div class="detail-row"><strong>Date of NR:</strong> {{ $inspection->date_of_nr ? \Carbon\Carbon::parse($inspection->date_of_nr)->format('Y-m-d') : '-' }}</div>
                                <div class="detail-row"><strong>Lapse 20 Day Period:</strong> {{ $inspection->lapse_20_day_period ? \Carbon\Carbon::parse($inspection->lapse_20_day_period)->format('Y-m-d') : '-' }}</div>
                                <div class="detail-row"><strong>TWG ALI:</strong> {{ $inspection->twg_ali ?? '-' }}</div>
                            @else
                                <p>No inspection data available.</p>
                            @endif
                        </div>
                        
                        <!-- Stage 2: Docketing -->
                        <div id="stage2-{{ $case->id }}" class="tab-content">
                            <h3 class="font-bold mb-3">Stage 2: Docketing Details</h3>
                            @if($docketing)
                                <div class="detail-row"><strong>PCT for Docketing:</strong> {{ $docketing->pct_for_docketing ?? '-' }}</div>
                                <div class="detail-row"><strong>Date Scheduled/Docketed:</strong> {{ $docketing->date_scheduled_docketed ? \Carbon\Carbon::parse($docketing->date_scheduled_docketed)->format('Y-m-d') : '-' }}</div>
                                <div class="detail-row"><strong>Aging Docket:</strong> {{ $docketing->aging_docket ?? '-' }}</div>
                                <div class="detail-row"><strong>Status Docket:</strong> {{ $docketing->status_docket ?? '-' }}</div>
                                <div class="detail-row"><strong>Hearing Officer MIS:</strong> {{ $docketing->hearing_officer_mis ?? '-' }}</div>
                            @else
                                <p>No docketing data available.</p>
                            @endif
                        </div>
                        
                        <!-- Stage 3: Hearing Process -->
                        <div id="stage3-{{ $case->id }}" class="tab-content">
                            <h3 class="font-bold mb-3">Stage 3: Hearing Process Details</h3>
                            @if($hearing)
                                <div class="detail-row"><strong>Date 1st MC Actual:</strong> {{ $hearing->date_1st_mc_actual ? \Carbon\Carbon::parse($hearing->date_1st_mc_actual)->format('Y-m-d') : '-' }}</div>
                                <div class="detail-row"><strong>First MC PCT:</strong> {{ $hearing->first_mc_pct ?? '-' }}</div>
                                <div class="detail-row"><strong>Status 1st MC:</strong> {{ $hearing->status_1st_mc ?? '-' }}</div>
                                <div class="detail-row"><strong>Date 2nd/Last MC:</strong> {{ $hearing->date_2nd_last_mc ? \Carbon\Carbon::parse($hearing->date_2nd_last_mc)->format('Y-m-d') : '-' }}</div>
                                <div class="detail-row"><strong>Second/Last MC PCT:</strong> {{ $hearing->second_last_mc_pct ?? '-' }}</div>
                                <div class="detail-row"><strong>Status 2nd MC:</strong> {{ $hearing->status_2nd_mc ?? '-' }}</div>
                                <div class="detail-row"><strong>Case Folder Forwarded to RO:</strong> {{ $hearing->case_folder_forwarded_to_ro ?? '-' }}</div>
                                <div class="detail-row"><strong>Complete Case Folder:</strong> {{ $hearing->complete_case_folder ?? '-' }}</div>
                            @else
                                <p>No hearing process data available.</p>
                            @endif
                        </div>
                        
                        <!-- Stage 4: Review & Drafting -->
                        <div id="stage4-{{ $case->id }}" class="tab-content">
                            <h3 class="font-bold mb-3">Stage 4: Review & Drafting Details</h3>
                            @if($reviewAndDrafting)
                                <div class="detail-row"><strong>Draft Order Type:</strong> {{ $reviewAndDrafting->draft_order_type ?? '-' }}</div>
                                <div class="detail-row"><strong>Applicable Draft Order:</strong> {{ $reviewAndDrafting->applicable_draft_order ?? '-' }}</div>
                                <div class="detail-row"><strong>PO PCT:</strong> {{ $reviewAndDrafting->po_pct ?? '-' }}</div>
                                <div class="detail-row"><strong>Aging PO PCT:</strong> {{ $reviewAndDrafting->aging_po_pct ?? '-' }}</div>
                                <div class="detail-row"><strong>Status PO PCT:</strong> {{ $reviewAndDrafting->status_po_pct ?? '-' }}</div>
                                <div class="detail-row"><strong>Date Received from PO:</strong> {{ $reviewAndDrafting->date_received_from_po ? \Carbon\Carbon::parse($reviewAndDrafting->date_received_from_po)->format('Y-m-d') : '-' }}</div>
                                <div class="detail-row"><strong>Reviewer/Drafter:</strong> {{ $reviewAndDrafting->reviewer_drafter ?? '-' }}</div>
                                <div class="detail-row"><strong>Date Received by Reviewer:</strong> {{ $reviewAndDrafting->date_received_by_reviewer ? \Carbon\Carbon::parse($reviewAndDrafting->date_received_by_reviewer)->format('Y-m-d') : '-' }}</div>
                                <div class="detail-row"><strong>Date Returned from Drafter:</strong> {{ $reviewAndDrafting->date_returned_from_drafter ? \Carbon\Carbon::parse($reviewAndDrafting->date_returned_from_drafter)->format('Y-m-d') : '-' }}</div>
                                <div class="detail-row"><strong>Aging 10 Days TSSD:</strong> {{ $reviewAndDrafting->aging_10_days_tssd ?? '-' }}</div>
                                <div class="detail-row"><strong>Status Reviewer/Drafter:</strong> {{ $reviewAndDrafting->status_reviewer_drafter ?? '-' }}</div>
                                <div class="detail-row"><strong>Draft Order TSSD Reviewer:</strong> {{ $reviewAndDrafting->draft_order_tssd_reviewer ?? '-' }}</div>
                            @else
                                <p>No review & drafting data available.</p>
                            @endif
                        </div>
                        
                        <!-- Stage 5: Orders & Disposition -->
                        <div id="stage5-{{ $case->id }}" class="tab-content">
                            <h3 class="font-bold mb-3">Stage 5: Orders & Disposition Details</h3>
                            @if($ordersAndDisposition)
                                <div class="detail-row"><strong>Aging 2 Days Finalization:</strong> {{ $ordersAndDisposition->aging_2_days_finalization ?? '-' }}</div>
                                <div class="detail-row"><strong>Status Finalization:</strong> {{ $ordersAndDisposition->status_finalization ?? '-' }}</div>
                                <div class="detail-row"><strong>PCT 96 Days:</strong> {{ $ordersAndDisposition->pct_96_days ?? '-' }}</div>
                                <div class="detail-row"><strong>Date Signed MIS:</strong> {{ $ordersAndDisposition->date_signed_mis ? \Carbon\Carbon::parse($ordersAndDisposition->date_signed_mis)->format('Y-m-d') : '-' }}</div>
                                <div class="detail-row"><strong>Status PCT:</strong> {{ $ordersAndDisposition->status_pct ?? '-' }}</div>
                                <div class="detail-row"><strong>Reference Date PCT:</strong> {{ $ordersAndDisposition->reference_date_pct ? \Carbon\Carbon::parse($ordersAndDisposition->reference_date_pct)->format('Y-m-d') : '-' }}</div>
                                <div class="detail-row"><strong>Aging PCT:</strong> {{ $ordersAndDisposition->aging_pct ?? '-' }}</div>
                                <div class="detail-row"><strong>Disposition MIS:</strong> {{ $ordersAndDisposition->disposition_mis ?? '-' }}</div>
                                <div class="detail-row"><strong>Disposition Actual:</strong> {{ $ordersAndDisposition->disposition_actual ?? '-' }}</div>
                                <div class="detail-row"><strong>Findings to Comply:</strong> {{ $ordersAndDisposition->findings_to_comply ?? '-' }}</div>
                                <div class="detail-row"><strong>Date of Order Actual:</strong> {{ $ordersAndDisposition->date_of_order_actual ? \Carbon\Carbon::parse($ordersAndDisposition->date_of_order_actual)->format('Y-m-d') : '-' }}</div>
                                <div class="detail-row"><strong>Released Date Actual:</strong> {{ $ordersAndDisposition->released_date_actual ? \Carbon\Carbon::parse($ordersAndDisposition->released_date_actual)->format('Y-m-d') : '-' }}</div>
                            @else
                                <p>No orders & disposition data available.</p>
                            @endif
                        </div>
                        
                        <!-- Stage 6: Compliance & Awards -->
                        <div id="stage6-{{ $case->id }}" class="tab-content">
                            <h3 class="font-bold mb-3">Stage 6: Compliance & Awards Details</h3>
                            @if($complianceAndAwards)
                                <div class="detail-row"><strong>Compliance Order Monetary Award:</strong> {{ $complianceAndAwards->compliance_order_monetary_award ? number_format($complianceAndAwards->compliance_order_monetary_award, 2) : '-' }}</div>
                                <div class="detail-row"><strong>OSH Penalty:</strong> {{ $complianceAndAwards->osh_penalty ? number_format($complianceAndAwards->osh_penalty, 2) : '-' }}</div>
                                <div class="detail-row"><strong>Affected Workers (Male):</strong> {{ $complianceAndAwards->affected_male ?? '-' }}</div>
                                <div class="detail-row"><strong>Affected Workers (Female):</strong> {{ $complianceAndAwards->affected_female ?? '-' }}</div>
                                <div class="detail-row"><strong>First Order Dismissal CNPC:</strong> {{ $complianceAndAwards->first_order_dismissal_cnpc ? 'Yes' : 'No' }}</div>
                                <div class="detail-row"><strong>Tavable Less Than 10 Workers:</strong> {{ $complianceAndAwards->tavable_less_than_10_workers ? 'Yes' : 'No' }}</div>
                                <div class="detail-row"><strong>With Deposited Monetary Claims:</strong> {{ $complianceAndAwards->with_deposited_monetary_claims ? 'Yes' : 'No' }}</div>
                                <div class="detail-row"><strong>Amount Deposited:</strong> {{ $complianceAndAwards->amount_deposited ? number_format($complianceAndAwards->amount_deposited, 2) : '-' }}</div>
                                <div class="detail-row"><strong>With Order Payment Notice:</strong> {{ $complianceAndAwards->with_order_payment_notice ? 'Yes' : 'No' }}</div>
                                <div class="detail-row"><strong>Status All Employees Received:</strong> {{ $complianceAndAwards->status_all_employees_received ?? '-' }}</div>
                                <div class="detail-row"><strong>Status Case After First Order:</strong> {{ $complianceAndAwards->status_case_after_first_order ?? '-' }}</div>
                                <div class="detail-row"><strong>Date Notice Finality Dismissed:</strong> {{ $complianceAndAwards->date_notice_finality_dismissed ? \Carbon\Carbon::parse($complianceAndAwards->date_notice_finality_dismissed)->format('Y-m-d') : '-' }}</div>
                                <div class="detail-row"><strong>Released Date Notice Finality:</strong> {{ $complianceAndAwards->released_date_notice_finality ? \Carbon\Carbon::parse($complianceAndAwards->released_date_notice_finality)->format('Y-m-d') : '-' }}</div>
                                <div class="detail-row"><strong>Updated/Ticked in MIS:</strong> {{ $complianceAndAwards->updated_ticked_in_mis ? 'Yes' : 'No' }}</div>
                                <div class="detail-row"><strong>Second Order Drafter:</strong> {{ $complianceAndAwards->second_order_drafter ?? '-' }}</div>
                                <div class="detail-row"><strong>Date Received by Drafter CT CNPC:</strong> {{ $complianceAndAwards->date_received_by_drafter_ct_cnpc ? \Carbon\Carbon::parse($complianceAndAwards->date_received_by_drafter_ct_cnpc)->format('Y-m-d') : '-' }}</div>
                            @else
                                <p>No compliance & awards data available.</p>
                            @endif
                        </div>
                        
                        <!-- Stage 7: Appeals & Resolution -->
                        <div id="stage7-{{ $case->id }}" class="tab-content">
                            <h3 class="font-bold mb-3">Stage 7: Appeals & Resolution Details</h3>
                            @if($appealsAndResolution)
                                <div class="detail-row"><strong>Date Returned Case Mgmt:</strong> {{ $appealsAndResolution->date_returned_case_mgmt ? \Carbon\Carbon::parse($appealsAndResolution->date_returned_case_mgmt)->format('Y-m-d') : '-' }}</div>
                                <div class="detail-row"><strong>Review CT CNPC:</strong> {{ $appealsAndResolution->review_ct_cnpc ?? '-' }}</div>
                                <div class="detail-row"><strong>Date Received Drafter Finalization (2nd):</strong> {{ $appealsAndResolution->date_received_drafter_finalization_2nd ? \Carbon\Carbon::parse($appealsAndResolution->date_received_drafter_finalization_2nd)->format('Y-m-d') : '-' }}</div>
                                <div class="detail-row"><strong>Date Returned Case Mgmt Signature (2nd):</strong> {{ $appealsAndResolution->date_returned_case_mgmt_signature_2nd ? \Carbon\Carbon::parse($appealsAndResolution->date_returned_case_mgmt_signature_2nd)->format('Y-m-d') : '-' }}</div>
                                <div class="detail-row"><strong>Date Order (2nd CNPC):</strong> {{ $appealsAndResolution->date_order_2nd_cnpc ? \Carbon\Carbon::parse($appealsAndResolution->date_order_2nd_cnpc)->format('Y-m-d') : '-' }}</div>
                                <div class="detail-row"><strong>Released Date (2nd CNPC):</strong> {{ $appealsAndResolution->released_date_2nd_cnpc ? \Carbon\Carbon::parse($appealsAndResolution->released_date_2nd_cnpc)->format('Y-m-d') : '-' }}</div>
                                <div class="detail-row"><strong>Date Forwarded MALSU:</strong> {{ $appealsAndResolution->date_forwarded_malsu ? \Carbon\Carbon::parse($appealsAndResolution->date_forwarded_malsu)->format('Y-m-d') : '-' }}</div>
                                <div class="detail-row"><strong>Motion Reconsideration Date:</strong> {{ $appealsAndResolution->motion_reconsideration_date ? \Carbon\Carbon::parse($appealsAndResolution->motion_reconsideration_date)->format('Y-m-d') : '-' }}</div>
                                <div class="detail-row"><strong>Date Received MALSU:</strong> {{ $appealsAndResolution->date_received_malsu ? \Carbon\Carbon::parse($appealsAndResolution->date_received_malsu)->format('Y-m-d') : '-' }}</div>
                                <div class="detail-row"><strong>Date Resolution MR:</strong> {{ $appealsAndResolution->date_resolution_mr ? \Carbon\Carbon::parse($appealsAndResolution->date_resolution_mr)->format('Y-m-d') : '-' }}</div>
                                <div class="detail-row"><strong>Released Date Resolution MR:</strong> {{ $appealsAndResolution->released_date_resolution_mr ? \Carbon\Carbon::parse($appealsAndResolution->released_date_resolution_mr)->format('Y-m-d') : '-' }}</div>
                                <div class="detail-row"><strong>Date Appeal Received Records:</strong> {{ $appealsAndResolution->date_appeal_received_records ? \Carbon\Carbon::parse($appealsAndResolution->date_appeal_received_records)->format('Y-m-d') : '-' }}</div>
                            @else
                                <p>No appeals & resolution data available.</p>
                            @endif
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

    // Tab Switching
    document.querySelectorAll('.tab').forEach(tab => {
        tab.addEventListener('click', () => {
            const accordion = tab.closest('.accordion-content');
            accordion.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
            accordion.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));

            tab.classList.add('active');
            const tabContentId = tab.getAttribute('data-tab');
            accordion.querySelector(`#${tabContentId}`).classList.add('active');
        });
    });

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