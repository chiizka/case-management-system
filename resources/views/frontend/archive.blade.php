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
    .text-red-600 {
        color: #dc2626;
    }
    .hover-text-red-800:hover {
        color: #991b1b;
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
    .ml-2 {
        margin-left: 0.5rem;
    }
.text-warning {
    color: #f39c12;
}

/* Appeal tab - only highlight when active */
.tab[data-tab^="appeal-details"].active {
    border-bottom: 2px solid #f39c12;
    color: #f39c12;
    background-color: #fffbf0; /* Very subtle background */
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
                            
                            {{-- Status with Appeal Badge --}}
                            <span class="font-bold">Status:</span> 
                            @if($case->overall_status === 'Appealed')
                                <span class="text-warning" style="background: #fff3cd; padding: 0.25rem 0.75rem; border-radius: 12px; font-weight: 600; font-size: 0.85rem;">
                                    <i class="fas fa-gavel"></i> {{ $case->overall_status }}
                                </span>
                                @if($case->appeal)
                                    <br>
                                    <span style="font-size: 0.875rem; color: #6c757d; margin-left: 3.5rem;">
                                        <i class="fas fa-arrow-right"></i> {{ $case->appeal->appellate_body }} ({{ $case->appeal->transmittal_date->format('M d, Y') }})
                                    </span>
                                @endif
                            @else
                                <span class="text-green-600">{{ $case->overall_status }}</span>
                            @endif
                        </div>
                        <div>
                            <button class="text-blue-600 hover-text-blue-800">View Details</button>
                            
                            @if(Auth::user()->isAdmin() || Auth::user()->isMalsu() || Auth::user()->isCaseManagement())
                                {{-- Hide Appeal button if already appealed --}}
                                @if($case->overall_status !== 'Appealed')
                                    <button class="text-red-600 hover-text-red-800 ml-2" data-toggle="modal" data-target="#appealModal" data-case-id="{{ $case->id }}">Appeal Case</button>
                                @endif
                            @endif
                        </div>
                    </div>
                    <div class="accordion-content p-4 bg-gray-50">
                        <!-- Main Tab Navigation -->
                        <div class="flex border-b mb-4" style="flex-wrap: wrap;">
                            <div class="tab active" data-tab="overview-{{ $case->id }}">Overview</div>
                            <div class="tab" data-tab="documents-{{ $case->id }}">Documents</div>
                            <div class="tab" data-tab="doc-history-{{ $case->id }}">Document History</div>

                            {{-- NEW: Appeal Details Tab (only show if appealed) - same style as others --}}
                            @if($case->appeal)
                                <div class="tab" data-tab="appeal-details-{{ $case->id }}">Appeal Details</div>
                            @endif

                        </div>
                        <!-- Overview Tab -->
                        <div id="overview-{{ $case->id }}" class="tab-content active">
                            <h3 class="font-bold mb-3" style="font-size: 1.25rem; color: #1f2937;">Case Information</h3>
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
                                <span class="detail-label">Mode:</span>
                                <span class="detail-value">{{ $case->mode ?? '-' }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">PO Office:</span>
                                <span class="detail-value">{{ $case->po_office ?? '-' }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Type of Industry:</span>
                                <span class="detail-value">{{ $case->type_of_industry ?? '-' }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Overall Status:</span>
                                <span class="detail-value text-green-600" style="font-weight: 600;">{{ $case->overall_status ?? '-' }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Date of Inspection:</span>
                                <span class="detail-value">{{ $case->date_of_inspection ? \Carbon\Carbon::parse($case->date_of_inspection)->format('Y-m-d') : '-' }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Name of Inspector:</span>
                                <span class="detail-value">{{ $case->inspector_name ?? '-' }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Authority No.:</span>
                                <span class="detail-value">{{ $case->inspector_authority_no ?? '-' }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Date of NR:</span>
                                <span class="detail-value">{{ $case->date_of_nr ? \Carbon\Carbon::parse($case->date_of_nr)->format('Y-m-d') : '-' }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Hearing Officer (MIS):</span>
                                <span class="detail-value">{{ $case->hearing_officer_mis ?? '-' }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">PCT (96 days from NR):</span>
                                <span class="detail-value">{{ $case->pct_96_days ? $case->pct_96_days->format('Y-m-d') : '-' }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Status (PO PCT):</span>
                                <span class="detail-value">{{ $case->status_po_pct ?? '-' }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Date Signed (MIS):</span>
                                <span class="detail-value">{{ $case->date_signed_mis ? \Carbon\Carbon::parse($case->date_signed_mis)->format('Y-m-d') : '-' }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Created At:</span>
                                <span class="detail-value">{{ $case->created_at ? \Carbon\Carbon::parse($case->created_at)->format('Y-m-d') : '-' }}</span>
                            </div>
                        </div>

                        <!-- Appeal Details Tab (only if appealed) -->
                        @if($case->appeal)
                            <div id="appeal-details-{{ $case->id }}" class="tab-content">
                                <div style="background: linear-gradient(135deg, #ffeaa7 0%, #fdcb6e 100%); padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem;">
                                    <h3 class="font-bold mb-2" style="font-size: 1.25rem; color: #2d3436; display: flex; align-items: center;">
                                        <i class="fas fa-gavel" style="margin-right: 0.5rem; font-size: 1.5rem;"></i>
                                        Appeal Information
                                    </h3>
                                    <p style="color: #2d3436; font-size: 0.875rem; margin: 0;">
                                        This case has been appealed and forwarded to the Central Office in Manila
                                    </p>
                                </div>
                                
                                <div class="detail-row">
                                    <span class="detail-label">Appeal Status:</span>
                                    <span class="detail-value" style="color: #d63031; font-weight: 600;">
                                        <i class="fas fa-exclamation-circle"></i> APPEALED
                                    </span>
                                </div>
                                
                                <div class="detail-row">
                                    <span class="detail-label">Appellate Body:</span>
                                    <span class="detail-value" style="font-weight: 600;">{{ $case->appeal->appellate_body }}</span>
                                </div>
                                
                                <div class="detail-row">
                                    <span class="detail-label">Transmittal Date:</span>
                                    <span class="detail-value">{{ $case->appeal->transmittal_date->format('F d, Y') }}</span>
                                </div>
                                
                                <div class="detail-row">
                                    <span class="detail-label">Destination:</span>
                                    <span class="detail-value">
                                        <i class="fas fa-map-marker-alt" style="color: #0984e3;"></i> 
                                        {{ $case->appeal->destination }}
                                    </span>
                                </div>
                                
                                <div class="detail-row">
                                    <span class="detail-label">Days Since Appeal:</span>
                                    <span class="detail-value">
                                        {{ $case->appeal->transmittal_date->diffInDays(now()) }} days ago
                                    </span>
                                </div>
                                
                                @if($case->appeal->notes)
                                    <div class="detail-row" style="border-top: 2px solid #fdcb6e; padding-top: 1rem; margin-top: 1rem;">
                                        <span class="detail-label">Appeal Notes:</span>
                                        <span class="detail-value" style="font-style: italic; color: #636e72;">
                                            "{{ $case->appeal->notes }}"
                                        </span>
                                    </div>
                                @endif
                                
                                <div class="detail-row" style="background: #f8f9fa; padding: 1rem; border-radius: 0.25rem; margin-top: 1.5rem;">
                                    <span class="detail-label">Recorded By:</span>
                                    <span class="detail-value">
                                        {{ Auth::user()->fname }} {{ Auth::user()->lname }}
                                        <br>
                                        <small class="text-muted">{{ $case->appeal->created_at->format('M d, Y h:i A') }}</small>
                                    </span>
                                </div>
                            </div>
                        @endif
                        
                        <!-- Documents Tab -->
                        <div id="documents-{{ $case->id }}" class="tab-content">
                            <h3 class="font-bold mb-3" style="font-size: 1.25rem; color: #1f2937;">Document Checklist</h3>

                            @php
                                $documents = $case->document_checklist ?? [];
                            @endphp

                            @if(count($documents) > 0)
                                @php
                                    $checkedCount = collect($documents)->where('checked', true)->count();
                                    $totalCount = count($documents);
                                @endphp

                                {{-- Progress Summary --}}
                                <div style="background: #f7fafc; border: 1px solid #e2e8f0; border-radius: 0.25rem; padding: 1rem; margin-bottom: 1.5rem;">
                                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; text-align: center;">
                                        <div>
                                            <div style="font-size: 1.5rem; font-weight: bold; color: #2b6cb0;">{{ $totalCount }}</div>
                                            <div style="font-size: 0.75rem; color: #718096; text-transform: uppercase;">Total Documents</div>
                                        </div>
                                        <div>
                                            <div style="font-size: 1.5rem; font-weight: bold; color: #2f855a;">{{ $checkedCount }}</div>
                                            <div style="font-size: 0.75rem; color: #718096; text-transform: uppercase;">Completed</div>
                                        </div>
                                        <div>
                                            <div style="font-size: 1.5rem; font-weight: bold; color: #c05621;">{{ $totalCount - $checkedCount }}</div>
                                            <div style="font-size: 0.75rem; color: #718096; text-transform: uppercase;">Pending</div>
                                        </div>
                                    </div>

                                    {{-- Progress Bar --}}
                                    @php $percent = $totalCount > 0 ? round(($checkedCount / $totalCount) * 100) : 0; @endphp
                                    <div style="margin-top: 1rem;">
                                        <div style="display: flex; justify-content: space-between; margin-bottom: 0.25rem;">
                                            <small style="color: #718096;">Completion</small>
                                            <small style="color: #718096;">{{ $percent }}%</small>
                                        </div>
                                        <div style="background: #e2e8f0; border-radius: 9999px; height: 8px;">
                                            <div style="background: #48bb78; height: 8px; border-radius: 9999px; width: {{ $percent }}%;"></div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Document List --}}
                                <ul style="list-style: none; padding: 0; margin: 0;">
                                    @foreach($documents as $doc)
                                        <li style="padding: 0.75rem; border: 1px solid #e2e8f0; border-radius: 0.25rem; margin-bottom: 0.5rem; background: white; display: flex; justify-content: space-between; align-items: center;">
                                            
                                            <div style="display: flex; align-items: center; gap: 0.75rem; flex: 1;">
                                                {{-- Check status icon --}}
                                                @if(!empty($doc['checked']))
                                                    <span style="color: #38a169; font-size: 1.1rem;" title="Completed">
                                                        <i class="fas fa-check-circle"></i>
                                                    </span>
                                                @else
                                                    <span style="color: #cbd5e0; font-size: 1.1rem;" title="Pending">
                                                        <i class="fas fa-circle"></i>
                                                    </span>
                                                @endif

                                                {{-- Document title --}}
                                                <span style="{{ !empty($doc['checked']) ? 'text-decoration: line-through; color: #a0aec0;' : 'color: #2d3748;' }}">
                                                    {{ $doc['title'] ?? 'Untitled Document' }}
                                                </span>
                                            </div>

                                            {{-- File info and download link --}}
                                            <div style="display: flex; align-items: center; gap: 0.75rem; flex-shrink: 0;">
                                                @if(!empty($doc['link']))
                                                    {{-- External link (Google Drive, etc.) --}}
                                                    @if(!empty($doc['link_label']))
                                                        <span style="font-size: 0.75rem; color: #718096;">
                                                            {{ $doc['link_label'] }}
                                                        </span>
                                                    @endif
                                                    <a href="{{ $doc['link'] }}"
                                                    target="_blank"
                                                    style="display: inline-flex; align-items: center; gap: 0.35rem; padding: 0.3rem 0.75rem; background: #ebf8ff; color: #2b6cb0; border: 1px solid #bee3f8; border-radius: 0.25rem; font-size: 0.8rem; text-decoration: none;"
                                                    title="Open link">
                                                        <i class="fas fa-external-link-alt"></i> Open
                                                    </a>
                                                    @if(!empty($doc['link_added_by']))
                                                        <span style="font-size: 0.7rem; color: #a0aec0;">
                                                            by {{ $doc['link_added_by'] }} on {{ $doc['link_added_at'] ?? '' }}
                                                        </span>
                                                    @endif
                                                @elseif(!empty($doc['file_path']) && !empty($doc['file_name']))
                                                    {{-- Uploaded file --}}
                                                    <span style="font-size: 0.75rem; color: #718096;">
                                                        {{ $doc['file_name'] }}
                                                        @if(!empty($doc['file_size']))
                                                            ({{ $doc['file_size'] }})
                                                        @endif
                                                    </span>
                                                    <a href="{{ url('/case/' . $case->id . '/documents/' . $doc['id'] . '/download') }}"
                                                    target="_blank"
                                                    style="display: inline-flex; align-items: center; gap: 0.35rem; padding: 0.3rem 0.75rem; background: #ebf8ff; color: #2b6cb0; border: 1px solid #bee3f8; border-radius: 0.25rem; font-size: 0.8rem; text-decoration: none;"
                                                    title="View/Download {{ $doc['file_name'] }}">
                                                        <i class="fas fa-download"></i> View
                                                    </a>
                                                @else
                                                    <span style="font-size: 0.75rem; color: #cbd5e0; font-style: italic;">No file attached</span>
                                                @endif
                                            </div>

                                        </li>
                                    @endforeach
                                </ul>

                            @else
                                <div style="text-align: center; padding: 2rem; color: #a0aec0;">
                                    <i class="fas fa-file-alt" style="font-size: 2rem; margin-bottom: 0.75rem; display: block;"></i>
                                    <p style="margin: 0;">No documents in checklist for this case.</p>
                                </div>
                            @endif
                        </div>

                        <!-- Document History Tab -->
                        <div id="doc-history-{{ $case->id }}" class="tab-content">
                            <h3 class="font-bold mb-3">Document Tracking History</h3>
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

<!-- Appeal Modal -->
<div class="modal fade" id="appealModal" tabindex="-1" role="dialog" aria-labelledby="appealModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title" id="appealModalLabel">
                    <i class="fas fa-gavel mr-2"></i> Appeal Archived Case
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="appealForm">
                @csrf
                <input type="hidden" id="appeal_case_id" name="case_id">
                <div class="modal-body">
                    <div class="alert alert-info">
                        <small><i class="fas fa-info-circle"></i> This case will be marked as "Appealed" and sent to the Central Office in Manila.</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="appellate_body" class="font-weight-bold">Appellate Body: <span class="text-danger">*</span></label>
                        <select id="appellate_body" name="appellate_body" class="form-control" required>
                            <option value="Office of the Secretary" selected>Office of the Secretary</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="transmittal_date" class="font-weight-bold">Transmittal Date (to Manila): <span class="text-danger">*</span></label>
                        <input type="date" id="transmittal_date" name="transmittal_date" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="font-weight-bold">Destination:</label>
                        <input type="text" class="form-control" value="Central Office - Manila" readonly disabled>
                    </div>
                    
                    <div class="form-group">
                        <label for="appeal_notes" class="font-weight-bold">Notes (Optional):</label>
                        <textarea id="appeal_notes" name="notes" class="form-control" rows="3" placeholder="Add tracking number or special instructions..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-gavel mr-2"></i> Submit Appeal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Export Modal for Archived Cases -->
<div class="modal fade" id="exportArchivedModal" tabindex="-1" role="dialog" aria-labelledby="exportModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="exportModalLabel">
                    <i class="fas fa-file-excel mr-2"></i> Export Archived Cases
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="mb-4">
                    <label class="font-weight-bold d-block mb-2">Export scope:</label>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="radio" name="exportScopeArchived" id="scopeVisibleArchived" value="visible" checked>
                        <label class="form-check-label" for="scopeVisibleArchived">
                            Current view (<span id="visibleCountArchived">0</span> cases)
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="exportScopeArchived" id="scopeAllArchived" value="all">
                        <label class="form-check-label" for="scopeAllArchived">
                            All archived cases on this page (<span id="totalCountArchived">0</span>)
                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label for="exportYearArchived" class="font-weight-bold d-block mb-2">Filter by year (optional):</label>
                    <select class="form-control" id="exportYearArchived">
                        <option value="">All years</option>
                        <option value="2026">2026</option>
                        <option value="2025">2025</option>
                        <option value="2024">2024</option>
                        <option value="2023">2023</option>
                        <option value="2022">2022</option>
                    </select>
                    <small class="form-text text-muted mt-1">
                        Based on <strong>Date Archived</strong> (updated_at)
                    </small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="confirmExportArchived">
                    <i class="fas fa-download mr-2"></i> Export to XLSX
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- SheetJS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<!-- FileSaver (for download trigger) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.5/FileSaver.min.js"></script>
<!-- SweetAlert2 (nice feedback) – optional but recommended -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
                        ${item.transferred_by === item.received_by && item.notes && item.notes.toLowerCase().includes('case created by') ? `
                            <div style="grid-column: 1 / -1;">
                                <small class="text-muted" style="display: block; margin-bottom: 0.25rem;">Created & Received By:</small>
                                <strong class="text-success" style="font-size: 0.875rem;">${item.received_by}</strong>
                                <br>
                                <small class="text-muted">${item.received_at}</small>
                            </div>
                        ` : `
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
                        `}
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

// Export Selected Function
function exportSelected() {
    // Count visible and total items
    const visibleItems = document.querySelectorAll('#caseList > div[data-case-id]:not([style*="display: none"])');
    const totalItems = document.querySelectorAll('#caseList > div[data-case-id]');

    document.getElementById('visibleCountArchived').textContent = visibleItems.length;
    document.getElementById('totalCountArchived').textContent = totalItems.length;

    // Show modal
    $('#exportArchivedModal').modal('show');
}

// Confirm export from modal
document.getElementById('confirmExportArchived').addEventListener('click', function () {
    const scope = document.querySelector('input[name="exportScopeArchived"]:checked').value;
    const yearFilter = document.getElementById('exportYearArchived').value;

    $('#exportArchivedModal').modal('hide');

    // Get all case cards
    let casesToExport = [];
    const allCases = document.querySelectorAll('#caseList > div[data-case-id]');

    if (scope === 'visible') {
        // Only visible (after search filter)
        allCases.forEach(caseEl => {
            if (caseEl.style.display !== 'none') {
                casesToExport.push(caseEl);
            }
        });
    } else {
        // All on page
        casesToExport = Array.from(allCases);
    }

    // Apply year filter (on Date Archived = updated_at shown in header)
    if (yearFilter) {
        casesToExport = casesToExport.filter(caseEl => {
            const dateArchivedText = caseEl.querySelector('.accordion-header').textContent;
            const dateMatch = dateArchivedText.match(/Date Archived:\s*(\d{4}-\d{2}-\d{2})/);
            if (dateMatch && dateMatch[1]) {
                return dateMatch[1].startsWith(yearFilter);
            }
            return false;
        });
    }

    if (casesToExport.length === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Nothing to export',
            text: yearFilter ? `No archived cases found for year ${yearFilter}` : 'No cases match your selection.',
            confirmButtonText: 'OK'
        });
        return;
    }

    // Comprehensive headers from ALL tabs
    const headers = [
        'No.',
        'Inspection ID',
        'Case No.',
        'Establishment Name',
        'Mode',
        'PO Office',
        'Type of Industry',
        'Overall Status',
        'Date of Inspection',
        'Name of Inspector',
        'Authority No.',
        'Date of NR',
        'Hearing Officer (MIS)',
        'PCT (96 days from NR)',
        'Status (PO PCT)',
        'Date Signed (MIS)',
        'Created At',
        'Date Archived'
    ];

    const exportData = [headers];

    casesToExport.forEach(caseEl => {
        const caseId = caseEl.dataset.caseId;
        const header = caseEl.querySelector('.accordion-header');
        
        // Get all tab content divs
        const overview = caseEl.querySelector('#overview-' + caseId);
        const inspection = caseEl.querySelector('#inspection-' + caseId);
        const docketing = caseEl.querySelector('#docketing-' + caseId);
        const hearing = caseEl.querySelector('#hearing-' + caseId);
        const review = caseEl.querySelector('#review-' + caseId);
        const orders = caseEl.querySelector('#orders-' + caseId);
        const compliance = caseEl.querySelector('#compliance-' + caseId);
        const appeals = caseEl.querySelector('#appeals-' + caseId);
        const additional = caseEl.querySelector('#additional-' + caseId);

        // Helper function to get detail value from any tab
        function getDetailValue(tabElement, labelText) {
            if (!tabElement) return '';
            const detailRows = tabElement.querySelectorAll('.detail-row');
            for (let row of detailRows) {
                const label = row.querySelector('.detail-label');
                if (label && label.textContent.includes(labelText)) {
                    const value = row.querySelector('.detail-value');
                    return value ? value.textContent.trim() : '';
                }
            }
            return '';
        }

        const dateArchived = header.textContent.match(/Date Archived:\s*(\d{4}-\d{2}-\d{2})/)?.[1] || '';

        const row = [
            getDetailValue(overview, 'No.:'),
            getDetailValue(overview, 'Inspection ID:'),
            getDetailValue(overview, 'Case No.:'),
            getDetailValue(overview, 'Establishment Name:'),
            getDetailValue(overview, 'Mode:'),
            getDetailValue(overview, 'PO Office:'),
            getDetailValue(overview, 'Type of Industry:'),
            getDetailValue(overview, 'Overall Status:'),
            getDetailValue(overview, 'Date of Inspection:'),
            getDetailValue(overview, 'Name of Inspector:'),
            getDetailValue(overview, 'Authority No.:'),
            getDetailValue(overview, 'Date of NR:'),
            getDetailValue(overview, 'Hearing Officer (MIS):'),
            getDetailValue(overview, 'PCT (96 days from NR):'),
            getDetailValue(overview, 'Status (PO PCT):'),
            getDetailValue(overview, 'Date Signed (MIS):'),
            getDetailValue(overview, 'Created At:'),
            dateArchived
        ];

        exportData.push(row);
    });

    // SheetJS export
    const ws = XLSX.utils.aoa_to_sheet(exportData);

    // Auto column width - ensure all columns are visible
    const colWidths = [];
    const numCols = exportData[0].length;
    
    for (let colIdx = 0; colIdx < numCols; colIdx++) {
        let maxLen = 10;
        
        if (exportData[0][colIdx]) {
            maxLen = Math.max(maxLen, exportData[0][colIdx].toString().length);
        }
        
        for (let rowIdx = 1; rowIdx < exportData.length; rowIdx++) {
            if (exportData[rowIdx][colIdx]) {
                const cellLen = exportData[rowIdx][colIdx].toString().length;
                if (cellLen > maxLen) {
                    maxLen = cellLen;
                }
            }
        }
        
        colWidths.push({ wch: Math.min(80, maxLen + 4) });
    }
    
    ws['!cols'] = colWidths;

    const wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, "Archived Cases");

    const today = new Date().toISOString().slice(0, 10);
    const yearPart = yearFilter ? `_${yearFilter}` : '';
    const scopePart = scope === 'visible' ? '_filtered' : '_all';
    const fileName = `Archived_Cases${scopePart}${yearPart}_${today}.xlsx`;

    XLSX.writeFile(wb, fileName);

    Swal.fire({
        icon: 'success',
        title: 'Export complete!',
        text: `${exportData.length - 1} archived cases exported`,
        timer: 2200,
        showConfirmButton: false
    });
});

// Appeal Case Modal Handler
$(document).on('click', '[data-target="#appealModal"]', function(e) {
    e.stopPropagation(); // Prevent accordion toggle
    const caseId = $(this).data('case-id');
    $('#appeal_case_id').val(caseId);
    
    // Reset form
    $('#appealForm')[0].reset();
});

// Submit Appeal Form
$('#appealForm').on('submit', function(e) {
    e.preventDefault();
    
    const caseId = $('#appeal_case_id').val();
    const formData = {
        appellate_body: $('#appellate_body').val(),
        transmittal_date: $('#transmittal_date').val(),
        notes: $('#appeal_notes').val(),
        _token: $('meta[name="csrf-token"]').attr('content')
    };
    
    // Disable submit button
    const submitBtn = $(this).find('button[type="submit"]');
    submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i> Processing...');
    
    $.ajax({
        url: `/archive/${caseId}/appeal`,
        method: 'POST',
        data: formData,
        success: function(response) {
            $('#appealModal').modal('hide');
            
            Swal.fire({
                icon: 'success',
                title: 'Appeal Submitted!',
                text: response.message,
                confirmButtonText: 'OK'
            }).then(() => {
                location.reload(); // Reload to show updated status
            });
        },
        error: function(xhr) {
            const errorMsg = xhr.responseJSON?.message || 'Failed to submit appeal. Please try again.';
            
            Swal.fire({
                icon: 'error',
                title: 'Appeal Failed',
                text: errorMsg,
                confirmButtonText: 'OK'
            });
            
            // Re-enable submit button
            submitBtn.prop('disabled', false).html('<i class="fas fa-gavel mr-2"></i> Submit Appeal');
        }
    });
});
</script>

@stop