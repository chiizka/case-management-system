@extends('frontend.layouts.app')
@section('content')

<style>
/* ==================== TABLE CONTAINER - TALLER ==================== */
.table-container {
    overflow-x: auto;
    overflow-y: auto;
    max-width: 100%;
    height: calc(100vh - 185px);
    border: 1px solid #dee2e6;
    border-radius: 0.35rem;
    position: relative;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    margin-bottom: 0;
}

/* Sticky Header */
.sticky-table thead th {
    position: sticky;
    top: 0;
    z-index: 30;
    background-color: #f8f9fc !important;
    box-shadow: 0 2px 5px rgba(0,0,0,0.15);
    white-space: nowrap;
    font-weight: 750;
    padding: 0.75rem 0.7rem;
}

/* ==================== STICKY LEFT COLUMNS ==================== */

/* 1. No. Column */
.table:not(.cm-table) th:nth-child(2),
.table:not(.cm-table) td:nth-child(2) {
    position: sticky;
    left: 0;
    z-index: 35;
    background-color: #f8f9fc !important;
    box-shadow: 3px 0 8px rgba(0,0,0,0.1);
    width: 75px;
    min-width: 75px;
    max-width: 75px;
}

/* 2. Inspection ID Column */
.table:not(.cm-table) th:nth-child(3),
.table:not(.cm-table) td:nth-child(3) {
    position: sticky;
    left: 75px;
    z-index: 35;
    background-color: #f8f9fc !important;
    box-shadow: 3px 0 8px rgba(0,0,0,0.1);
    width: 110px;
    min-width: 110px;
    max-width: 110px;
}

/* 3. Case No. Column */
.table:not(.cm-table) th:nth-child(4),
.table:not(.cm-table) td:nth-child(4) {
    position: sticky;
    left: 185px;
    z-index: 35;
    background-color: #fff3cd !important;
    box-shadow: 3px 0 8px rgba(0,0,0,0.1);
    width: 100px;
    min-width: 100px;
    max-width: 100px;
}

/* 4. Establishment Name Column (UPDATED: Added explicit font-weight overrides) */
.table:not(.cm-table) th:nth-child(5),
.table:not(.cm-table) td:nth-child(5) {
    position: sticky;
    left: 285px;
    z-index: 35;
    background-color: #d1ecf1 !important;
    box-shadow: 3px 0 8px rgba(0,0,0,0.1);
    min-width: 200px;
    max-width: 340px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    font-weight: bold !important; /* Forces bold tracking across headers and table bodies */
}

/* PO column (7th column) */
.table:not(.cm-table) th:nth-child(7),
.table:not(.cm-table) td:nth-child(7) {
    background-color: #d4edda !important; /* green for PO */
}

/* ==================== COMPACT TABLE - SHORTER ROWS ==================== */
.compact-table {
    font-size: 0.85rem;
    table-layout: fixed;
    width: 100%;
    min-width: 100%;
    border-collapse: collapse;
}

.compact-table th,
.compact-table td {
    padding: 0.45rem 0.7rem;
    vertical-align: middle;
    border-right: 1px solid #dee2e6;
    min-height: 38px;
    height: auto;
    line-height: 1.4;
}

/* ==================== WRAP COLUMNS ==================== */
.wrap-cell {
    white-space: normal !important;
    word-break: break-word;
    overflow: visible !important;
    text-overflow: unset !important;
    max-width: 220px;
    min-width: 140px;
    line-height: 1.4;
}

/* ==================== ACTIONS CELL ==================== */
.actions-cell {
    padding: 0.4rem 0.6rem !important;
    white-space: nowrap;
    vertical-align: middle;
    min-width: 68px;
    transition: all 0.25s ease;
}

.actions-cell.collapsed {
    width: 68px;
    min-width: 68px;
    max-width: 68px;
}

.actions-cell.expanded {
    width: auto !important;
    min-width: 320px !important;
    max-width: 380px !important;
}

.action-buttons-container {
    display: flex !important;
    align-items: center;
    gap: 6px;
    flex-wrap: nowrap;
    width: fit-content !important;
    max-width: 100%;
    margin: 0 !important;
    padding: 0 !important;
}

.actions-cell.expanded .action-buttons-container {
    width: 100% !important;
    justify-content: flex-start !important;
}

.action-toggle-btn {
    background: #007bff;
    color: white;
    border: none;
    border-radius: 6px;
    padding: 6px 10px;
    cursor: pointer;
    font-size: 0.9rem;
    min-width: 34px;
    height: 34px;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
}

.action-toggle-btn:hover {
    background: #0056b3;
}

.action-buttons {
    display: flex;
    gap: 6px;
    align-items: center;
    flex-wrap: nowrap;
}

.action-buttons .btn {
    width: 34px;
    height: 34px;
    padding: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.05rem;
    border-radius: 6px;
}

.actions-cell.collapsed .action-buttons {
    display: none;
}

.actions-cell.edit-mode-cell {
    width: 68px !important;
    min-width: 68px !important;
    max-width: 68px !important;
}

.actions-cell.edit-mode-cell .action-buttons {
    display: none !important;
}

.action-toggle-btn.save-mode {
    background: #28a745;
}
.action-toggle-btn.save-mode:hover {
    background: #1e7e34;
}
.action-toggle-btn.exit-mode {
    background: #dc3545;
}
.action-toggle-btn.exit-mode:hover {
    background: #bd2130;
}

/* ==================== OTHER STYLES ==================== */
.custom-search-container {
    margin-bottom: 1rem;
}

.custom-search-container input {
    font-size: 0.8rem;
}

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

.readonly-cell {
    background-color: #f8f9fa !important;
    color: #6c757d;
    font-style: italic;
}

.readonly-cell:hover {
    background-color: #e9ecef !important;
}

.readonly-cell::after { content: none !important; }

.compact-table .btn {
    font-size: 0.65rem;
    padding: 0.2rem 0.4rem;
    margin: 0 0.1rem;
}

.compact-table .badge {
    font-size: 0.65rem;
    padding: 0.2rem 0.4rem;
}

/* Scrollbar - Neutral gray */
.table-container::-webkit-scrollbar {
    height: 10px;
    width: 10px;
}

.table-container::-webkit-scrollbar-thumb {
    background: #adb5bd;
    border-radius: 10px;
}

.table-container::-webkit-scrollbar-thumb:hover {
    background: #6c757d;
}

/* Smooth transitions */
.editable-cell, .readonly-cell {
    transition: background-color 0.3s ease, color 0.3s ease;
}

/* ==================== FIX FOR DATATABLES OVERRIDE ==================== */
.actions-cell.expanded.sorting_1 {
    width: auto !important;
    min-width: 320px !important;
    max-width: 380px !important;
    padding: 0.4rem 0.6rem !important;
    white-space: nowrap !important;
}

.actions-cell.expanded .action-buttons-container {
    width: 100% !important;
    justify-content: flex-start !important;
    gap: 6px !important;
}

.actions-cell.expanded .action-buttons {
    display: flex !important;
    gap: 6px !important;
    flex-wrap: nowrap !important;
}

td.actions-cell.expanded {
    box-sizing: border-box !important;
    overflow: hidden !important;
}

.actions-cell.expanded,
.actions-cell.expanded.sorting_1 {
    width: fit-content !important;
    min-width: fit-content !important;
    max-width: fit-content !important;
    padding-right: 8px !important;
}

.action-buttons-container {
    justify-content: flex-start !important;
}

#dataTableTabsContent .card {
    margin-bottom: 0 !important;
}

#dataTableTabsContent .card-body {
    padding-bottom: 0 !important;
}

#content-wrapper {
    min-height: unset !important;
    height: 100vh !important;
    overflow: hidden !important;
}

#content {
    overflow: hidden !important;
    flex: 1 !important;
}

#content .container-fluid {
    padding-bottom: 0 !important;
}

#content {
    padding-bottom: 0 !important;
    margin-bottom: 0 !important;
}

#content .container-fluid {
    padding-top: 0 !important;
}

#dataTableTabsContent {
    margin-top: 0 !important;
}

.dataTables_wrapper .dataTables_info {
    float: left !important;
    padding-top: 0.5rem !important;
}

.dataTables_wrapper .dataTables_paginate {
    float: right !important;
}

.dataTables_wrapper::after {
    content: '';
    display: table;
    clear: both;
}

/* ==================== CM TABLE STICKY COLUMNS ==================== */
.cm-table th:nth-child(2),
.cm-table td:nth-child(2) {
    position: sticky;
    left: 0;
    z-index: 35;
    background-color: #f8f9fc !important;
    box-shadow: 3px 0 8px rgba(0,0,0,0.1);
    width: 75px;
    min-width: 75px;
    max-width: 75px;
}

.cm-table th:nth-child(3),
.cm-table td:nth-child(3) {
    position: sticky;
    left: 75px;
    z-index: 35;
    background-color: #f8f9fc !important;
    box-shadow: 3px 0 8px rgba(0,0,0,0.1);
    width: 110px;
    min-width: 110px;
    max-width: 110px;
}

.cm-table th:nth-child(4),
.cm-table td:nth-child(4) {
    position: sticky;
    left: 185px;
    z-index: 35;
    background-color: #fff3cd !important;
    box-shadow: 3px 0 8px rgba(0,0,0,0.1);
    width: 100px;
    min-width: 100px;
    max-width: 100px;
}

/* 4. CM xTable Establishment Name Column (UPDATED: Added explicit font-weight overrides) */
.cm-table th:nth-child(5),
.cm-table td:nth-child(5) {
    position: sticky;
    left: 285px;
    z-index: 35;
    background-color: #d1ecf1 !important;
    box-shadow: 3px 0 8px rgba(0,0,0,0.1);
    min-width: 200px;
    max-width: 340px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    font-weight: bold !important; /* Forces bold tracking inside Case Management context */
}

.cm-table th:nth-child(7),
.cm-table td:nth-child(7) {
    background-color: #d4edda !important;
}
</style>

<!-- Main Content -->
<div id="content">
    <!-- Begin Page Content -->
    <div class="container-fluid">

        @php
            $user = Auth::user();
            $isProvincialCM = $user->isProvincialCaseManagement();
        @endphp

        @if(Auth::user()->isSheriff() || Auth::user()->isCaseManagement() || Auth::user()->isMalsu() || Auth::user()->isAdmin())

            <ul class="nav nav-tabs mb-0" id="dataTableTabs" role="tablist">

                @if(Auth::user()->isSheriff())
                <li class="nav-item">
                    <a class="nav-link active" id="tabSheriff-tab" data-toggle="tab" href="#tabSheriff"
                    role="tab" aria-controls="tabSheriff" aria-selected="true">
                        <i class="fas fa-briefcase mr-1"></i> My Cases
                    </a>
                </li>
                @endif

                @if(Auth::user()->isCaseManagement() && !$isProvincialCM)
                <li class="nav-item">
                    <a class="nav-link" id="tabCM-tab" data-toggle="tab" href="#tabCM"
                    role="tab" aria-controls="tabCM" aria-selected="false">
                        <i class="fas fa-briefcase mr-1"></i> My Cases
                    </a>
                </li>
                @endif

              @if(!Auth::user()->isMalsu() && !Auth::user()->isSheriff() && !$isProvincialCM)
                <li class="nav-item">
                    <a class="nav-link active" 
                    id="tab0-tab" data-toggle="tab" href="#tab0"
                    role="tab" aria-controls="tab0" aria-selected="true">
                        <i class="fas fa-folder-open mr-1"></i> All Active Cases
                    </a>
                </li>
                @endif

                @if(Auth::user()->isCaseManagement())
                @php
                $provinceTabs = [
                    'albay'          => 'Albay',
                    'camarines_sur'  => 'Cam Sur',
                    'camarines_norte'=> 'Cam Norte',
                    'catanduanes'    => 'Catanduanes',
                    'masbate'        => 'Masbate',
                    'sorsogon'       => 'Sorsogon',
                ];
                @endphp
                @foreach($provinceTabs as $key => $label)
                    @if(Auth::user()->isProvincialCaseManagementFor($key))
                    <li class="nav-item">
                        <a class="nav-link {{ $isProvincialCM ? 'active' : '' }}" id="tabProv-{{ $key }}-tab"
                        data-toggle="tab" href="#tabProv-{{ $key }}"
                        role="tab" aria-controls="tabProv-{{ $key }}" aria-selected="false">
                            <i class="fas fa-map-marker-alt mr-1"></i> {{ $label }}
                        </a>
                    </li>
                    @endif
                @endforeach
                @endif

                @if(Auth::user()->isMalsu() || Auth::user()->isAdmin())
                <li class="nav-item">
                    <a class="nav-link" id="tabMALSU-tab" data-toggle="tab" href="#tabMALSU"
                    role="tab" aria-controls="tabMALSU" aria-selected="false">
                        <i class="fas fa-briefcase mr-1"></i> My Cases
                    </a>
                </li>
                @endif

                @if(Auth::user()->isMalsu() || Auth::user()->isAdmin())
                @php
                $sheriffProvinceTabs = [
                    'albay'           => 'Albay',
                    'camarines_sur'   => 'Cam Sur',
                    'camarines_norte' => 'Cam Norte',
                    'catanduanes'     => 'Catanduanes',
                    'masbate'         => 'Masbate',
                    'sorsogon'        => 'Sorsogon',
                ];
                @endphp
                @foreach($sheriffProvinceTabs as $key => $label)
                <li class="nav-item">
                    <a class="nav-link" id="tabSheriffProv-{{ $key }}-tab"
                    data-toggle="tab" href="#tabSheriffProv-{{ $key }}"
                    role="tab" aria-controls="tabSheriffProv-{{ $key }}" aria-selected="false">
                        <i class="fas fa-user-shield mr-1"></i> {{ $label }} Sheriff
                    </a>
                </li>
                @endforeach
                @endif

                @if(Auth::user()->isMalsu() || Auth::user()->isAdmin())
                <li class="nav-item">
                    <a class="nav-link" id="tabSENA-tab" data-toggle="tab" href="#tabSENA"
                    role="tab" aria-controls="tabSENA" aria-selected="false">
                        <i class="fas fa-gavel mr-1"></i> SENA
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="tabResolution-tab" data-toggle="tab" href="#tabResolution"
                    role="tab" aria-controls="tabResolution" aria-selected="false">
                        <i class="fas fa-check-circle mr-1"></i>For Resolution
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="tabAppealed-tab" data-toggle="tab" href="#tabAppealed"
                    role="tab" aria-controls="tabAppealed" aria-selected="false">
                        <i class="fas fa-balance-scale mr-1"></i> Appealed Cases
                    </a>
                </li>
                @endif

            </ul>
        @endif

            <!-- Tabs Content -->
            <div class="tab-content mt-1" id="dataTableTabsContent">
        
        <!-- Tab 0: All Active Cases (Enhanced with corrected columns) -->
        @if(!Auth::user()->isMalsu() && !Auth::user()->isSheriff() && !$isProvincialCM)
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
                    <div class="table-container" id="tab0-table-container" style="display: none;">
                        <!-- Loading spinner for Tab 0 -->
                        <div id="tab0-loading" class="text-center" style="padding: 3rem;">
                            <div class="spinner-border text-primary mb-3" role="status" style="width: 3rem; height: 3rem;">
                                <span class="sr-only">Loading...</span>
                            </div>
                            <p class="text-muted">Loading active cases...</p>
                            <small class="text-muted">This may take a moment for the first load</small>
                        </div>
                        <table class="table table-bordered compact-table sticky-table" id="dataTable0" style="min-width: 100%;">
                            <thead>
                                <tr>
                                    <th>Actions</th>
                                    {{-- Core Information --}}
                                    <th>No.</th>
                                    <th>Inspection ID</th>
                                    <th>Case No.</th>
                                    <th>Establishment Name</th>
                                    <th>Mode</th>
                                    <th>PO</th>
                                    <th>Type of Industry</th>
                            
                                    {{-- Inspection Stage --}}
                                    <th>Date of Inspection</th>
                                    <th>Name of Inspector</th>
                                    <th>Authority No.</th>
                                    <th>Date of NR</th>
                                    <th>Lapse 20 Day Correction Period</th>
                            
                                    {{-- Docketing Stage --}}
                                    <th>PCT for Docketing</th>
                                    <th>Date Scheduled/Docketed</th>
                                    <th>Aging (Docket)</th>
                                    <th>Status (Docket)</th>
                                    <th>Hearing Officer (MIS)</th>
                            
                                    {{-- Hearing Process Stage --}}
                                    <th>Date of 1st MC (Actual)</th>
                                    <th>1st MC PCT</th>
                                    <th>Status (1st MC)</th>
                                    <th>Date of 2nd/Last MC (Actual)</th>
                                    <th>2nd/Last MC PCT</th>
                                    <th>Status (2nd MC)</th>
                                    <th>Case Folder Forwarded to RO</th>
                            
                                    {{-- Review & Drafting --}}
                                    <th>PO PCT</th>
                                    <th>Aging (PO PCT)</th>
                                    <th>Status (PO PCT)</th>
                            
                                    {{-- Orders & Disposition --}}
                                    <th>PCT (96 days from NR)</th>
                                    <th>Status (PCT)</th>
                                    <th>Date Signed (MIS)</th>
                            
                                    <th>Created At</th>
                                </tr>
                            </thead>
                            <tbody>

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        @endif

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
    
                @if((Auth::user()->isCaseManagement() && !$isProvincialCM) || Auth::user()->isAdmin())
                    <!-- Tab CM: Case Management's Cases (LAZY LOAD) -->
                    <div class="tab-pane fade" id="tabCM" role="tabpanel" aria-labelledby="tabCM-tab">
                        <div class="card shadow mb-4">
                            <div class="card-body">
                                <div class="tab-loading text-center" style="padding: 3rem;">
                                    <div class="spinner-border text-primary mb-3" role="status"
                                        style="width: 3rem; height: 3rem;">
                                        <span class="sr-only">Loading...</span>
                                    </div>
                                    <p class="text-muted">Loading cases assigned to Case Management...</p>
                                    <small class="text-muted">This may take a moment for the first load</small>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                @if(Auth::user()->isCaseManagement())
                @foreach(['albay','camarines_sur','camarines_norte','catanduanes','masbate','sorsogon'] as $provKey)
                @if(Auth::user()->isProvincialCaseManagementFor($provKey))
                @php
                $provLabels = [
                    'albay'          => 'Albay',
                    'camarines_sur'  => 'Camarines Sur',
                    'camarines_norte'=> 'Camarines Norte',
                    'catanduanes'    => 'Catanduanes',
                    'masbate'        => 'Masbate',
                    'sorsogon'       => 'Sorsogon',
                ];
                @endphp
                <div class="tab-pane fade {{ $isProvincialCM ? 'show active' : '' }}" id="tabProv-{{ $provKey }}"
                    role="tabpanel" aria-labelledby="tabProv-{{ $provKey }}-tab">
                    <div class="card shadow mb-4">
                        <div class="card-body">
                            <div class="tab-loading text-center" style="padding: 3rem;">
                                <div class="spinner-border text-primary mb-3" role="status"
                                    style="width: 3rem; height: 3rem;">
                                    <span class="sr-only">Loading...</span>
                                </div>
                                <p class="text-muted">Loading {{ $provLabels[$provKey] }} cases...</p>
                                <small class="text-muted">This may take a moment for the first load</small>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
                @endforeach
                @endif

                @if(Auth::user()->isSheriff())
                <div class="tab-pane fade show active" id="tabSheriff" role="tabpanel" aria-labelledby="tabSheriff-tab">
                    <div class="card shadow mb-4">
                        <div class="card-body">
                            <div class="tab-loading text-center" style="padding: 3rem;">
                                <div class="spinner-border text-primary mb-3" role="status" style="width: 3rem; height: 3rem;">
                                    <span class="sr-only">Loading...</span>
                                </div>
                                <p class="text-muted">Loading your assigned cases...</p>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                @if(Auth::user()->isMalsu() || Auth::user()->isAdmin())
                    <div class="tab-pane fade {{ Auth::user()->isMalsu() ? 'show active' : '' }}" 
                        id="tabMALSU" role="tabpanel" aria-labelledby="tabMALSU-tab">
                        <div class="card shadow mb-4">
                            <div class="card-body">
                                <div class="tab-loading text-center" style="padding: 3rem;">
                                    <div class="spinner-border text-primary mb-3" role="status"
                                        style="width: 3rem; height: 3rem;">
                                        <span class="sr-only">Loading...</span>
                                    </div>
                                    <p class="text-muted">Loading cases assigned to MALSU...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                @if(Auth::user()->isMalsu() || Auth::user()->isAdmin())
                @foreach(['albay','camarines_sur','camarines_norte','catanduanes','masbate','sorsogon'] as $provKey)
                @php
                $sheriffProvLabels = [
                    'albay'           => 'Albay',
                    'camarines_sur'   => 'Camarines Sur',
                    'camarines_norte' => 'Camarines Norte',
                    'catanduanes'     => 'Catanduanes',
                    'masbate'         => 'Masbate',
                    'sorsogon'        => 'Sorsogon',
                ];
                @endphp
                <div class="tab-pane fade" id="tabSheriffProv-{{ $provKey }}"
                    role="tabpanel" aria-labelledby="tabSheriffProv-{{ $provKey }}-tab">
                    <div class="card shadow mb-4">
                        <div class="card-body">
                            <div class="tab-loading text-center" style="padding: 3rem;">
                                <div class="spinner-border text-primary mb-3" role="status"
                                    style="width: 3rem; height: 3rem;">
                                    <span class="sr-only">Loading...</span>
                                </div>
                                <p class="text-muted">Loading {{ $sheriffProvLabels[$provKey] }} sheriff cases...</p>
                                <small class="text-muted">This may take a moment for the first load</small>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
                @endif

                @if(Auth::user()->isMalsu() || Auth::user()->isAdmin())
                <div class="tab-pane fade" id="tabSENA" role="tabpanel" aria-labelledby="tabSENA-tab">
                    <div class="card shadow mb-4">
                        <div class="card-body">
                            <div class="text-center py-5 text-muted">
                                <i class="fas fa-gavel fa-3x mb-3 d-block"></i>
                                <h5>CENA</h5>
                                <p>Content coming soon.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="tabResolution" role="tabpanel" aria-labelledby="tabResolution-tab">
                    <div class="card shadow mb-4">
                        <div class="card-body">
                            <div class="text-center py-5 text-muted">
                                <i class="fas fa-check-circle fa-3x mb-3 d-block"></i>
                                <h5>Resolution</h5>
                                <p>Content coming soon.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="tabAppealed" role="tabpanel" aria-labelledby="tabAppealed-tab">
                    <div class="card shadow mb-4">
                        <div class="card-body">
                            <div class="text-center py-5 text-muted">
                                <i class="fas fa-balance-scale fa-3x mb-3 d-block"></i>
                                <h5>Appealed Cases</h5>
                                <p>Content coming soon.</p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            </div>
            <!-- End Tabs Content -->
        </div>
        <!-- /.container-fluid -->
    </div>
<!-- End of Main Content -->

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

                        {{-- ✨ FIRST FIELD: Type of Industry --}}
                        <div class="form-group">
                            <label for="type_of_industry">Type of Industry <span class="text-danger">*</span></label>
                            <select class="form-control" id="type_of_industry" name="type_of_industry" required>
                                <option value="">-- Select Type of Industry --</option>

                                <optgroup label="1. Retail Establishments">
                                    <option value="Retail - Specifics">Specifics</option>
                                    <option value="Retail - Sales Methods">Sales Methods</option>
                                </optgroup>

                                <optgroup label="2. Food Service Establishments">
                                    <option value="Food Service - Specifics">Specifics</option>
                                    <option value="Food Service - Includes">Includes</option>
                                    <option value="Food Service - Drinking Establishments">Drinking Establishments</option>
                                </optgroup>

                                <optgroup label="3. Professional Service Establishments">
                                    <option value="Professional - Legal & Finance">Legal &amp; Finance</option>
                                    <option value="Professional - Technical & Design">Technical &amp; Design</option>
                                    <option value="Professional - Consulting & Management">Consulting &amp; Management</option>
                                    <option value="Professional - Creative & Media">Creative &amp; Media</option>
                                    <option value="Professional - Rent">Rent</option>
                                </optgroup>

                                <optgroup label="4. Healthcare Industry">
                                    <option value="Healthcare - Specifics">Specifics</option>
                                </optgroup>

                                <optgroup label="5. Non-Agricultural Establishment">
                                    <option value="Non-Agricultural - Construction (Principal)">Construction - Principal</option>
                                    <option value="Non-Agricultural - Construction (Contractor)">Construction - Contractor</option>
                                    <option value="Non-Agricultural - Manufacturing">Manufacturing</option>
                                    <option value="Non-Agricultural - Mining & Quarrying">Mining &amp; Quarrying</option>
                                    <option value="Non-Agricultural - Energy Sector">Energy Sector</option>
                                    <option value="Non-Agricultural - Transportation & Logistics">Transportation &amp; Logistics</option>
                                    <option value="Non-Agricultural - Telecommunications">Telecommunications</option>
                                    <option value="Non-Agricultural - BPO">BPO</option>
                                </optgroup>

                                <optgroup label="6. Agriculture Establishment">
                                    <option value="Agriculture - Agriculture">Agriculture</option>
                                    <option value="Agriculture - Plantation">Plantation</option>
                                    <option value="Agriculture - Non-Plantation">Non-Plantation</option>
                                    <option value="Agriculture - Fishing/Marine Industry">Fishing/Marine Industry</option>
                                    <option value="Agriculture - Horticultural">Horticultural</option>
                                    <option value="Agriculture - Animal Farming">Animal Farming</option>
                                </optgroup>

                                <optgroup label="7. Other">
                                    <option value="Other - Please specify in remarks">Other (Please specify in the column)</option>
                                </optgroup>
                            </select>
                             </select>
                            <small class="form-text text-muted">
                                <i class="fas fa-info-circle"></i> Select the industry category that best describes the establishment
                            </small>
                            
                            {{-- shown only when Other is selected --}}
                            <div id="otherIndustryNote" class="alert alert-warning mt-2 py-2" style="display:none; font-size:0.85rem;">
                                <i class="fas fa-exclamation-circle"></i>
                                You selected <strong>Other</strong> — please specify the industry type in the
                                <strong>Type of Industry</strong> column after saving this case.
                            </div>
                        </div>

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

                        <div class="form-group">
                            <label for="po_office">Provincial Office <span class="text-danger">*</span></label>

                            @if(Auth::user()->isProvince())
                                <input type="text"
                                    class="form-control"
                                    id="po_office"
                                    name="po_office"
                                    value="{{ Auth::user()->getProvinceName() }}"
                                    readonly
                                    style="background-color: #e9ecef;">
                                <small class="form-text text-muted">
                                    <i class="fas fa-info-circle"></i> Automatically set to your province office
                                </small>
                            @else
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

                        <input type="hidden" name="current_stage" value="1: Inspections">
                        <input type="hidden" name="overall_status" value="Active">

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
                    <div id="executeDeliverySection" style="display: none;">
                        <hr>
                        <p class="mb-2" style="font-size: 0.85rem; font-weight: 600; color: #495057;">
                            <i class="fas fa-truck mr-1"></i> Delivery receipt details
                            <span class="badge badge-danger ml-1" style="font-size: 0.7rem;">Required</span>
                        </p>

                        <div class="form-group mb-2">
                            <label class="small mb-1">
                                Received by (respondent's name) <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                class="form-control form-control-sm"
                                id="execReceivedBy"
                                placeholder="Full name of the person who received the order">
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-2">
                                    <label class="small mb-1">
                                        Date &amp; time received <span class="text-danger">*</span>
                                    </label>
                                    <input type="datetime-local"
                                        class="form-control form-control-sm"
                                        id="execDateReceived">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-2">
                                    <label class="small mb-1">
                                        Tracking number <span class="text-danger">*</span>
                                    </label>
                                    <input type="text"
                                        class="form-control form-control-sm"
                                        id="execTrackingNo"
                                        placeholder="e.g. LBC123456789">
                                </div>
                            </div>
                        </div>

                        <div class="form-group mb-0">
                            <label class="small mb-1">
                                Courier / delivery method <span class="text-danger">*</span>
                            </label>
                            <select class="form-control form-control-sm" id="execCourier">
                                <option value="">Select courier…</option>
                                <option value="LBC">LBC</option>
                                <option value="Ninjavan">Ninjavan</option>
                                <option value="J&T Express">J&amp;T Express</option>
                                <option value="2GO">2GO</option>
                                <option value="PHLPost">PHLPost</option>
                                <option value="DHL">DHL</option>
                                <option value="Personal Service">Personal service</option>
                                <option value="Sheriff">Sheriff / court process server</option>
                                <option value="Other">Other</option>
                            </select>
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
        <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
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

    <!-- Add Link Modal -->
<div class="modal fade" id="addLinkModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-link"></i> Add Link</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="linkDocId">
                <div class="form-group">
                    <label for="linkUrl">URL <span class="text-danger">*</span></label>
                    <input type="url" class="form-control" id="linkUrl"
                           placeholder="https://drive.google.com/...">
                    <small class="form-text text-muted">Google Drive, OneDrive, SharePoint, etc.</small>
                </div>
                <div class="form-group">
                    <label for="linkLabel">Display Label</label>
                    <input type="text" class="form-control" id="linkLabel"
                           placeholder="e.g. Google Drive, SharePoint">
                    <small class="form-text text-muted">Optional. Defaults to "Open Link".</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmAddLinkBtn">
                    <i class="fas fa-save"></i> Save Link
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
let caseToProgress = null;
let caseToDelete = null;
let sheriffTabLoaded = false;
$(document).ready(function() {
    console.log('jQuery version:', $.fn.jquery);
    console.log('DataTables available:', typeof $.fn.DataTable !== 'undefined');
    
    $(document).ready(function() {
    // If a province tab is active by default (provincial Case Management users),
    // manually trigger its data load — shown.bs.tab never fires for an already-active tab
    var initialTabId = $('.tab-pane.show.active').attr('id');

    if (initialTabId && initialTabId.indexOf('tabProv-') === 0) {
        var provinceKey = initialTabId.replace('tabProv-', '');
        // Small delay to make sure loadProvinceTabData is defined and DOM is ready
        setTimeout(function() {
            loadProvinceTabData(provinceKey);
        }, 50);
    }
    });
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
    
    const doc = documents.find(d => d.id == docId);
    if (doc) {
        doc.checked = isChecked;
        
        const $label = $(`label[for="doc-${doc.id}"]`);
        
        if (isChecked) {
            $label.addClass('text-muted').css('text-decoration', 'line-through');
        } else {
            $label.removeClass('text-muted').css('text-decoration', 'none');
        }
        
        saveDocuments();
    }
});

// 5. REMOVE DOCUMENT BUTTON
$(document).on('click', '.remove-document-btn', function() {
    const docId = parseInt($(this).data('doc-id'));
    
    const doc = documents.find(d => d.id == docId);
    const hasLink = doc && doc.link;
    
    // Different confirmation messages based on whether file exists
    let confirmMessage = hasFile 
        ? 'This document has an uploaded file. Remove document and delete the file?' 
        : 'Remove this document from checklist?';
    
    if (!confirm(confirmMessage)) {
        return;
    }
    
    console.log('Removing document:', docId);
    
    // Remove document from array
    documents = documents.filter(d => d.id != docId);
    console.log('Documents after removal:', documents);
    
    // Save and re-render
    saveDocuments();
    renderDocuments();
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
        const hasLink = doc.link && doc.link.trim() !== '';
        
        console.log(`Rendering doc ${doc.id}: checked=${doc.checked}, hasFile=${hasFile}`);
        
        let fileInfo = '';
        let uploadButton = '';
        
        if (hasLink) {
            fileInfo = `
                <div class="file-info mt-1" style="text-decoration: none !important; font-style: normal !important;">
                    <i class="fas fa-link text-primary" style="text-decoration: none !important;"></i>
                    <a href="${doc.link}" target="_blank" class="ml-1 mr-2" 
                    title="${doc.link}"
                    style="text-decoration: underline !important;">
                        ${doc.link_label || 'Open Link'}
                    </a>
                    <small class="text-muted" style="text-decoration: none !important;">
                        (added by ${doc.link_added_by || 'unknown'} on ${doc.link_added_at || ''})
                    </small>
                    <button class="btn btn-sm btn-outline-danger delete-link-btn ml-2"
                            data-doc-id="${doc.id}"
                            title="Remove link"
                            type="button">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            `;
        } else {
                    uploadButton = `
                <button class="btn btn-sm btn-outline-primary add-link-btn"
                        data-doc-id="${doc.id}"
                        title="Add link"
                        type="button">
                    <i class="fas fa-link"></i> Add Link
                </button>
            `;
        }
        
        const item = `
        <li class="list-group-item d-flex justify-content-between align-items-start" 
            style="text-decoration: none !important;">
            <div class="document-content" style="text-decoration: none !important;">
                <div class="custom-control custom-checkbox">
                    <input type="checkbox" 
                        class="custom-control-input document-checkbox" 
                        id="doc-${doc.id}" 
                        data-doc-id="${doc.id}"
                        ${isChecked ? 'checked' : ''}>
                    <label class="custom-control-label ${isChecked ? 'text-muted' : ''}" 
                        for="doc-${doc.id}" 
                        style="${isChecked ? 'text-decoration: line-through;' : 'text-decoration: none;'}">
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

    $cell.toggleClass('collapsed expanded');

    const isNowExpanded = $cell.hasClass('expanded');
    $btn.find('i')
        .removeClass('fa-chevron-right fa-chevron-left')
        .addClass(isNowExpanded ? 'fa-chevron-left' : 'fa-chevron-right');

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

        // ✅ Explicitly set correct icon
        $cell.find('.action-toggle-btn i')
            .removeClass('fa-chevron-right fa-chevron-left')
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

        // ✅ Explicitly set to chevron-right, don't toggle
        $cell.find('.action-toggle-btn i')
            .removeClass('fa-chevron-right fa-chevron-left')
            .addClass('fa-chevron-right');

        setTimeout(() => {
            dt.columns.adjust();
            dt.draw(false);
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
        scrollY: (window.innerHeight - 280) + 'px',
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

            // If table has no real data, fix colspan and skip DataTable init
            const $tbody = $(tableId + ' tbody');
            const hasRealData = $tbody.find('tr').length > 0 && 
                                $tbody.find('tr td[colspan]').length === 0;

            if (!hasRealData) {
                // Fix the colspan to span all headers
                const colCount = $(tableId + ' thead th').length;
                $tbody.find('tr td').attr('colspan', colCount);
                // Remove fixed table layout so headers render correctly
                $(tableId).css('table-layout', 'auto');
                console.log('Table has no data, skipping DataTable init:', tableId);
                return false;
            }

            if ($.fn.DataTable.isDataTable(tableId)) {
                $(tableId).DataTable().destroy();
            }

            $(tableId).off();

            tables[tableId] = $(tableId).DataTable(dtConfig);
            console.log('✓ Initialized ' + tableId);

            bindSearchForTable(tableId);

            return true;
        } catch (error) {
            console.error('✗ Failed to initialize ' + tableId + ':', error);
            return false;
        }
    }

        // Open add-link modal
    $(document).on('click', '.add-link-btn', function() {
        const docId = parseInt($(this).data('doc-id'));
        $('#linkDocId').val(docId);
        $('#linkUrl').val('');
        $('#linkLabel').val('');
        $('#addLinkModal').modal('show');
    });

    // Save the link
    $('#confirmAddLinkBtn').on('click', function() {
        const docId = parseInt($('#linkDocId').val());
        const url   = $('#linkUrl').val().trim();
        const label = $('#linkLabel').val().trim();

        if (!url) {
            alert('Please enter a URL.');
            return;
        }

        try { new URL(url); } catch(e) {
            alert('Please enter a valid URL (must start with http:// or https://).');
            return;
        }

        const doc = documents.find(d => d.id == docId);
        if (doc) {
            doc.link        = url;
            doc.link_label  = label || 'Open Link';
            doc.link_added_at = new Date().toISOString().slice(0, 10); // just YYYY-MM-DD
            doc.link_added_by = '{{ Auth::user()->fname }} {{ Auth::user()->lname }}';
        }

        $('#addLinkModal').modal('hide');
        saveDocuments();
        renderDocuments();
        showToast('success', 'Link saved successfully.');
    });

    // Delete the link
    $(document).on('click', '.delete-link-btn', function() {
        if (!confirm('Remove this link from the document?')) return;

        const docId = parseInt($(this).data('doc-id'));
        const doc = documents.find(d => d.id == docId);
        if (doc) {
            delete doc.link;
            delete doc.link_label;
            delete doc.uploaded_at;
            delete doc.uploaded_by;
        }

        saveDocuments();
        renderDocuments();
        showToast('success', 'Link removed.');
    });

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

        // ── Case Management tab lazy load ──────────────────────────────────────
    var cmTabLoaded = false;
    var malsuTabLoaded = false;
    var provTabLoaded = {};
    var sheriffProvTabLoaded = {};

    // ── Province tabs lazy load ──
    $('a[id^="tabProv-"]').on('shown.bs.tab', function () {
        var province = this.id.replace('tabProv-', '').replace('-tab', '');
        var tableId  = '#dataTableProv-' + province;
        var searchId = '#customSearchProv-' + province;
        var $cardBody = $('#tabProv-' + province + ' .card-body');

        if (provTabLoaded[province]) {
            if ($.fn.DataTable.isDataTable(tableId)) {
                $(tableId).DataTable().columns.adjust().draw(false);
            }
            return;
        }

        $cardBody.html(`
            <div class="tab-loading text-center" style="padding: 3rem;">
                <div class="spinner-border text-primary mb-3" role="status"
                    style="width: 3rem; height: 3rem;">
                    <span class="sr-only">Loading...</span>
                </div>
                <p class="text-muted">Loading cases...</p>
            </div>
        `);

        $.ajax({
            url: '/case/load-province-tab/' + province,
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Accept': 'application/json'
            },
            success: function (response) {
                if (response.success) {
                    $cardBody.html(response.html);
                    provTabLoaded[province] = true;

                    setTimeout(function () {
                        if ($.fn.DataTable.isDataTable(tableId)) {
                            $(tableId).DataTable().destroy();
                        }

                        $(tableId + ' tbody tr td[colspan]').closest('tr').remove();

                        var provTable = $(tableId).DataTable({
                            pageLength: 10,
                            lengthChange: false,
                            paging: true,
                            searching: true,
                            info: true,
                            dom: 'tip',
                            columnDefs: [{ orderable: false, targets: 0 }],
                            scrollX: true,
                            scrollY: (window.innerHeight - 280) + 'px',
                            scrollCollapse: true,
                            language: {
                                emptyTable: 'No active cases currently at this provincial office.'
                            },
                            drawCallback: function () {
                                $(tableId + ' thead th').css({
                                    'position': 'sticky',
                                    'top': 0,
                                    'z-index': 12
                                });
                                $(tableId + ' thead th:nth-child(-n+5)').css('z-index', 13);
                            }
                        });

                        $(searchId).off('keyup input change').on('keyup input change', function () {
                            provTable.search(this.value).draw();
                        });

                        setTimeout(function () { provTable.columns.adjust().draw(false); }, 50);
                        setTimeout(function () { provTable.columns.adjust().draw(false); }, 300);

                    }, 100);

                } else {
                    $cardBody.html('<div class="alert alert-danger">Failed to load data. Please try again.</div>');
                }
            },
            error: function (xhr) {
                $cardBody.html(`
                    <div class="alert alert-danger">
                        <strong>Error:</strong> ${xhr.responseJSON?.error || 'Failed to load data.'}
                    </div>
                `);
            }
        });
    });

    $('a[href="#tabSheriff"]').on('shown.bs.tab', function () {
    if (sheriffTabLoaded) {
        if ($.fn.DataTable.isDataTable('#dataTableMALSU')) {
            $('#dataTableMALSU').DataTable().columns.adjust().draw(false);
        }
        return;
    }

    const $cardBody = $('#tabSheriff .card-body');

    $cardBody.html(`
        <div class="tab-loading text-center" style="padding: 3rem;">
            <div class="spinner-border text-primary mb-3" role="status" style="width: 3rem; height: 3rem;">
                <span class="sr-only">Loading...</span>
            </div>
            <p class="text-muted">Loading your assigned cases...</p>
        </div>
    `);

    $.ajax({
        url: '/case/load-sheriff-tab',
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            'Accept': 'application/json'
        },
        success: function (response) {
            if (response.success) {
                $cardBody.html(response.html);
                sheriffTabLoaded = true;

                setTimeout(function () {
                    if ($.fn.DataTable.isDataTable('#dataTableMALSU')) {
                        $('#dataTableMALSU').DataTable().destroy();
                    }

                    $('#dataTableMALSU tbody tr td[colspan]').closest('tr').remove();

                    tables['#dataTableMALSU'] = $('#dataTableMALSU').DataTable({
                        pageLength: 10,
                        lengthChange: false,
                        paging: true,
                        searching: true,
                        info: true,
                        dom: 'tip',
                        columnDefs: [{ orderable: false, targets: 0 }],
                        scrollX: true,
                        scrollY: (window.innerHeight - 280) + 'px',
                        scrollCollapse: true,
                        language: {
                            emptyTable: 'No cases are currently assigned to you.'
                        },
                        drawCallback: function() {
                            $('.sticky-table thead th, #dataTableCM thead th, #dataTableMALSU thead th').css({
                                'position': 'sticky',
                                'top': 0,
                                'z-index': 12
                            });
                            $('.sticky-table thead th:nth-child(-n+5), #dataTableCM thead th:nth-child(-n+5), #dataTableMALSU thead th:nth-child(-n+5)').css({
                                'z-index': 13
                            });
                        }
                    });

                    $('#customSearchMALSU').off('keyup input change').on('keyup input change', function () {
                        tables['#dataTableMALSU'].search(this.value).draw();
                    });

                    setTimeout(function() { tables['#dataTableMALSU'].columns.adjust().draw(false); }, 50);
                    setTimeout(function() { tables['#dataTableMALSU'].columns.adjust().draw(false); }, 300);
                    setTimeout(function() { tables['#dataTableMALSU'].columns.adjust().draw(false); }, 600);
                    setTimeout(function() {
                        tables['#dataTableMALSU'].columns.adjust().draw(false);
                        if (tables['#dataTable0']) {
                            tables['#dataTable0'].draw(false);
                        }
                    }, 200);

                }, 100);
            } else {
                $cardBody.html(`<div class="alert alert-danger">Failed to load cases. Please try again.</div>`);
            }
        },
        error: function (xhr) {
            $cardBody.html(`
                <div class="alert alert-danger">
                    <strong>Error:</strong> ${xhr.responseJSON?.error || 'Failed to load data.'}
                </div>
            `);
        }
    });
});

    $('a[href="#tabMALSU"]').on('shown.bs.tab', function () {
        if (malsuTabLoaded) {
            if ($.fn.DataTable.isDataTable('#dataTableMALSU')) {
                $('#dataTableMALSU').DataTable().columns.adjust().draw(false);
            }
            return;
        }

        const $cardBody = $('#tabMALSU .card-body');

        $cardBody.html(`
            <div class="tab-loading text-center" style="padding: 3rem;">
                <div class="spinner-border text-primary mb-3" role="status" style="width: 3rem; height: 3rem;">
                    <span class="sr-only">Loading...</span>
                </div>
                <p class="text-muted">Loading cases assigned to MALSU...</p>
            </div>
        `);

        $.ajax({
            url: '/case/load-malsu-tab',
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Accept': 'application/json'
            },
            success: function (response) {
                if (response.success) {
                    $cardBody.html(response.html);
                    malsuTabLoaded = true;

                    setTimeout(function () {
                        if ($.fn.DataTable.isDataTable('#dataTableMALSU')) {
                            $('#dataTableMALSU').DataTable().destroy();
                        }

                        $('#dataTableMALSU tbody tr td[colspan]').closest('tr').remove();

                        tables['#dataTableMALSU'] = $('#dataTableMALSU').DataTable({
                            pageLength: 10,
                            lengthChange: false,
                            paging: true,
                            searching: true,
                            info: true,
                            dom: 'tip',
                            columnDefs: [{ orderable: false, targets: 0 }],
                            scrollX: true,
                            scrollY: (window.innerHeight - 280) + 'px',
                            scrollCollapse: true,
                            language: {
                                emptyTable: 'No cases are currently assigned to MALSU.'
                            },
                            drawCallback: function() {
                                $('.sticky-table thead th, #dataTableCM thead th, #dataTableMALSU thead th').css({
                                    'position': 'sticky',
                                    'top': 0,
                                    'z-index': 12
                                });
                                $('.sticky-table thead th:nth-child(-n+5), #dataTableCM thead th:nth-child(-n+5), #dataTableMALSU thead th:nth-child(-n+5)').css({
                                    'z-index': 13
                                });
                            }
                        });

                        $('#customSearchMALSU').off('keyup input change').on('keyup input change', function () {
                            tables['#dataTableMALSU'].search(this.value).draw();
                        });

                            setTimeout(function() { tables['#dataTableMALSU'].columns.adjust().draw(false); }, 50);
                            setTimeout(function() { tables['#dataTableMALSU'].columns.adjust().draw(false); }, 300);
                            setTimeout(function() { tables['#dataTableMALSU'].columns.adjust().draw(false); }, 600);
                            setTimeout(function() {
                                tables['#dataTableMALSU'].columns.adjust().draw(false);
                                if (tables['#dataTable0']) {
                                    tables['#dataTable0'].draw(false);
                                }
                            }, 200);

                    }, 100);
                } else {
                    $cardBody.html(`<div class="alert alert-danger">Failed to load MALSU cases. Please try again.</div>`);
                }
            },
            error: function (xhr) {
                $cardBody.html(`
                    <div class="alert alert-danger">
                        <strong>Error:</strong> ${xhr.responseJSON?.error || 'Failed to load data.'}
                    </div>
                `);
            }
        });
    });

    ['albay','camarines_sur','camarines_norte','catanduanes','masbate','sorsogon'].forEach(function(province) {
        var tabSelector = '#tabSheriffProv-' + province;
        var tableId  = 'dataTableSheriff-' + province;
        var searchId = 'customSearchSheriff-' + province;

        $('a[href="' + tabSelector + '"]').on('shown.bs.tab', function () {
            if (sheriffProvTabLoaded[province]) {
                if ($.fn.DataTable.isDataTable('#' + tableId)) {
                    $('#' + tableId).DataTable().columns.adjust().draw(false);
                }
                return;
            }

            const $cardBody = $(tabSelector + ' .card-body');

            $cardBody.html(`
                <div class="tab-loading text-center" style="padding: 3rem;">
                    <div class="spinner-border text-primary mb-3" role="status" style="width: 3rem; height: 3rem;">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <p class="text-muted">Loading cases...</p>
                </div>
            `);

            $.ajax({
                url: '/case/load-sheriff-province-tab/' + province,
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'Accept': 'application/json'
                },
                success: function (response) {
                    if (response.success) {
                        $cardBody.html(response.html);
                        sheriffProvTabLoaded[province] = true;

                        setTimeout(function () {
                            if ($.fn.DataTable.isDataTable('#' + tableId)) {
                                $('#' + tableId).DataTable().destroy();
                            }

                            $('#' + tableId + ' tbody tr td[colspan]').closest('tr').remove();

                            tables['#' + tableId] = $('#' + tableId).DataTable({
                                pageLength: 10,
                                lengthChange: false,
                                paging: true,
                                searching: true,
                                info: true,
                                dom: 'tip',
                                columnDefs: [{ orderable: false, targets: 0 }],
                                scrollX: true,
                                scrollY: (window.innerHeight - 280) + 'px',
                                scrollCollapse: true,
                                language: {
                                    emptyTable: 'No cases are currently assigned to this province.'
                                },
                                drawCallback: function() {
                                    $('.sticky-table thead th, #dataTableCM thead th, #dataTableMALSU thead th, ' +
                                    '#dataTableSheriff-albay thead th, #dataTableSheriff-camarines_sur thead th, ' +
                                    '#dataTableSheriff-camarines_norte thead th, #dataTableSheriff-catanduanes thead th, ' +
                                    '#dataTableSheriff-masbate thead th, #dataTableSheriff-sorsogon thead th').css({
                                        'position': 'sticky',
                                        'top': 0,
                                        'z-index': 12
                                    });
                                    $('.sticky-table thead th:nth-child(-n+5), #dataTableCM thead th:nth-child(-n+5), #dataTableMALSU thead th:nth-child(-n+5), ' +
                                    '#dataTableSheriff-albay thead th:nth-child(-n+5), #dataTableSheriff-camarines_sur thead th:nth-child(-n+5), ' +
                                    '#dataTableSheriff-camarines_norte thead th:nth-child(-n+5), #dataTableSheriff-catanduanes thead th:nth-child(-n+5), ' +
                                    '#dataTableSheriff-masbate thead th:nth-child(-n+5), #dataTableSheriff-sorsogon thead th:nth-child(-n+5)').css({
                                        'z-index': 13
                                    });
                                }
                            });

                            $('#' + searchId).off('keyup input change').on('keyup input change', function () {
                                tables['#' + tableId].search(this.value).draw();
                            });

                            setTimeout(function() { tables['#' + tableId].columns.adjust().draw(false); }, 50);
                            setTimeout(function() { tables['#' + tableId].columns.adjust().draw(false); }, 300);
                            setTimeout(function() { tables['#' + tableId].columns.adjust().draw(false); }, 600);
                            setTimeout(function() {
                                tables['#' + tableId].columns.adjust().draw(false);
                                if (tables['#dataTable0']) {
                                    tables['#dataTable0'].draw(false);
                                }
                            }, 200);
                        }, 100);
                    } else {
                        $cardBody.html(`<div class="alert alert-danger">Failed to load cases. Please try again.</div>`);
                    }
                },
                error: function (xhr) {
                    $cardBody.html(`
                        <div class="alert alert-danger">
                            <strong>Error:</strong> ${xhr.responseJSON?.error || 'Failed to load data.'}
                        </div>
                    `);
                }
            });
        });
    });

    $('a[href="#tabCM"]').on('shown.bs.tab', function () {
        if (cmTabLoaded) {
            // Already loaded — just re-init the DataTable if needed
            if ($.fn.DataTable.isDataTable('#dataTableCM')) {
                $('#dataTableCM').DataTable().columns.adjust().draw(false);
            }
            return;
        }

        const $cardBody = $('#tabCM .card-body');

        $cardBody.html(`
            <div class="tab-loading text-center" style="padding: 3rem;">
                <div class="spinner-border text-primary mb-3" role="status" style="width: 3rem; height: 3rem;">
                    <span class="sr-only">Loading...</span>
                </div>
                <p class="text-muted">Loading cases assigned to Case Management...</p>
            </div>
        `);

        $.ajax({
            url: '/case/load-case-management-tab',
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Accept': 'application/json'
            },
            success: function (response) {
                if (response.success) {
                    $cardBody.html(response.html);
                    cmTabLoaded = true;

                    // Small delay to let DOM settle before DataTable init
                    setTimeout(function () {
                        if ($.fn.DataTable.isDataTable('#dataTableCM')) {
                            $('#dataTableCM').DataTable().destroy();
                        }

                        // Remove colspan "no data" row before DataTables touches it
                        $('#dataTableCM tbody tr td[colspan]').closest('tr').remove();

                        var cmTable = $('#dataTableCM').DataTable({
                            pageLength: 10,
                            lengthChange: false,
                            paging: true,
                            searching: true,
                            info: true,
                            dom: 'tip',
                            columnDefs: [{ orderable: false, targets: 0 }],
                            scrollX: true,
                            scrollY: (window.innerHeight - 280) + 'px',
                            scrollCollapse: true,
                            language: {
                                emptyTable: 'No cases are currently assigned to Case Management.'
                            },
                            drawCallback: function() {
                                $('.sticky-table thead th, #dataTableCM thead th').css({
                                    'position': 'sticky',
                                    'top': 0,
                                    'z-index': 12
                                });
                                $('.sticky-table thead th:nth-child(-n+5), #dataTableCM thead th:nth-child(-n+5)').css({
                                    'z-index': 13
                                });
                            }
                        });
                        // Bind search box
                        $('#customSearchCM').off('keyup input change').on('keyup input change', function () {
                            cmTable.search(this.value).draw();
                        });

                        // ── FIX: Force column recalculation after tab is fully visible ──
                        setTimeout(function() {
                            cmTable.columns.adjust().draw(false);
                        }, 50);

                        setTimeout(function() {
                            cmTable.columns.adjust().draw(false);
                        }, 300);

                    }, 100);
                } else {
                    $cardBody.html(`
                        <div class="alert alert-danger">
                            Failed to load Case Management cases. Please try again.
                        </div>
                    `);
                }
            },
            error: function (xhr) {
                $cardBody.html(`
                    <div class="alert alert-danger">
                        <strong>Error:</strong> ${xhr.responseJSON?.error || 'Failed to load data.'}
                    </div>
                `);
            }
        });
    });

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
    // ── Lazy load Tab 0 on page load ──────────────────────────────────
function loadTab0Data() {
    if ($.fn.DataTable.isDataTable('#dataTable0')) {
        $('#dataTable0').DataTable().destroy();
    }

    $.ajax({
        url: '/case/load-tab/0',
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            'Accept': 'application/json'
        },
        success: function(response) {
            if (response.success) {
                $('#dataTable0 tbody').html(response.html);
                $('#tab0-loading').hide();
                $('#tab0-table-container').show();

                tables['#dataTable0'] = $('#dataTable0').DataTable({
                    pageLength: 10,
                    lengthChange: false,
                    paging: true,
                    searching: true,
                    info: true,
                    dom: 'tip',
                    columnDefs: [{ orderable: false, targets: 0 }],
                    scrollX: true,
                    scrollY: (window.innerHeight - 280) + 'px',
                    scrollCollapse: true,
                    drawCallback: function() {
                        $('#dataTable0 thead th').css({
                            'position': 'sticky',
                            'top': '0',
                            'z-index': '12'
                        });
                        $('#dataTable0 thead th:nth-child(1)').css('z-index', '40');
                        $('#dataTable0 thead th:nth-child(2)').css('z-index', '40');
                        $('#dataTable0 thead th:nth-child(3)').css('z-index', '40');
                        $('#dataTable0 thead th:nth-child(4)').css('z-index', '40');
                        $('#dataTable0 thead th:nth-child(5)').css('z-index', '40');
                    }
                });

                $('#customSearch0').off('keyup input change').on('keyup input change', function() {
                    tables['#dataTable0'].search(this.value).draw();
                });

                console.log('✓ Tab 0 loaded with', response.count, 'records');
            } else {
                $('#tab0-loading').html('<div class="alert alert-danger">Failed to load cases. Please refresh.</div>');
            }
        },
        error: function(xhr) {
            console.error('Tab 0 load error:', xhr);
            $('#tab0-loading').html('<div class="alert alert-danger"><strong>Error:</strong> ' + (xhr.responseJSON?.error || 'Failed to load data.') + '</div>');
        }
    });
}

// Trigger on page load (only once)
loadTab0Data();

    // Show/hide "Other" note in Add Case modal
    $('#type_of_industry').on('change', function() {
        if ($(this).val().startsWith('Other')) {
            $('#otherIndustryNote').slideDown(200);
        } else {
            $('#otherIndustryNote').slideUp(200);
        }
    });

    // Reset the note when modal closes
    $('#addCaseModal').on('hidden.bs.modal', function() {
        $('#otherIndustryNote').hide();
    });
    
    // Adjust table after auto-minimize
    setTimeout(function() {
        if (tables['#dataTable0']) {
            tables['#dataTable0'].draw(false);
        }
    }, 200);

    // Tab switching with lazy loading
    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        var target = $(e.target).attr("href");
        var tableId = target.replace('#tab', '#dataTable');
        var tabId = target.replace('#', '');
        var tabNumber = tabId.replace('tab', '');

        // ── Skip the CM tab — it has its own dedicated handler ──
        if (tabId === 'tabCM' || tabId === 'tabMALSU' || tabId === 'tabSheriff' || tabId.startsWith('tabProv-') || tabId.startsWith('tabSheriffProv-')) return;

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

    // Dedicated handler for province tabs (Case Management)
    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        var target = $(e.target).attr("href");
        var tabId = target.replace('#', '');

            if (tabId.startsWith('tabProv-')) {
                var provinceKey = tabId.replace('tabProv-', '');
                loadProvinceTabData(provinceKey);
            }
        });

    function loadProvinceTabData(provinceKey) {
        var tabId = 'tabProv-' + provinceKey;
        var tableId = '#dataTableProv-' + provinceKey; // ⚠️ confirm this matches your partial's <table id="...">

        if (loadedTabs[tabId]) {
            if (tables[tableId]) {
                tables[tableId].columns.adjust().draw(false);
            }
            return;
        }

        var $cardBody = $('#' + tabId + ' .card-body');

        $.ajax({
            url: '/case/load-province-tab/' + provinceKey,
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Accept': 'application/json'
            },
            success: function(response) {
                if (response.success) {
                    $cardBody.html(response.html);
                    loadedTabs[tabId] = true;

                    setTimeout(function() {
                        initDataTable(tableId);
                    }, 100);
                } else {
                    $cardBody.html('<div class="alert alert-danger">Failed to load data.</div>');
                }
            },
            error: function(xhr) {
                console.error('Province tab load error:', xhr);
                $cardBody.html('<div class="alert alert-danger">Error loading province cases.</div>');
            }
        });
    }

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

    $('#confirmStageBtn').off('click').on('click', function() {
        console.log('=== CONFIRM STAGE PROGRESSION ===');
        console.log('caseToProgress:', caseToProgress);
        
        if (!caseToProgress || !caseToProgress.id) {
            console.error('No case selected for progression');
            showAlert('error', 'No case selected');
            return;
        }
        
        const button = $(this);
        const isForceComplete = caseToProgress.action === 'complete';
        const isDispose       = caseToProgress.action === 'dispose';
        const isExecute       = caseToProgress.action === 'execute';
            
        console.log('Is Force Complete:', isForceComplete);
        console.log('Is Dispose:', isDispose);
        console.log('Is Execute:', isExecute);
        console.log('Button classes:', caseToProgress.button.attr('class'));

        // ── EXECUTE BLOCK MUST BE FIRST ────────────────────────────
        if (isExecute) {
            const receivedBy   = $('#execReceivedBy').val().trim();
            const dateReceived = $('#execDateReceived').val().trim();
            const trackingNo   = $('#execTrackingNo').val().trim();
            const courier      = $('#execCourier').val().trim();

            if (!receivedBy || !dateReceived || !trackingNo || !courier) {
                showAlert('error', 'Please fill in all delivery receipt fields before confirming.');
                return;
            }

            button.prop('disabled', true);
            button.html('<i class="fas fa-spinner fa-spin"></i> Forwarding...');

            $.ajax({
                url: `/case/${caseToProgress.id}/execute`,
                method: 'POST',
                data: {
                    _token:             '{{ csrf_token() }}',
                    exec_received_by:   receivedBy,
                    exec_date_received: dateReceived,
                    exec_tracking_no:   trackingNo,
                    exec_courier:       courier,
                    notes:              'Case forwarded for execution.'
                },
                success: function(response) {
                    $('#stageProgressionModal').modal('hide');
                    if (response.success) {
                        showAlert('success', response.message);
                        $(`tr[data-id="${caseToProgress.id}"]`).fadeOut(400, function() {
                            $(this).remove();
                        });
                    } else {
                        showAlert('error', response.message || 'Failed to execute case.');
                    }
                },
                error: function(xhr) {
                    const message = xhr.responseJSON?.message || 'Failed to execute case.';
                    showAlert('error', message);
                },
                complete: function() {
                    button.prop('disabled', false)
                        .html('<i class="fas fa-check mr-2"></i>Confirm');
                }
            });
            return; // stops here, never reaches /archive
        }
        // ── END EXECUTE BLOCK ──────────────────────────────────────

        button.prop('disabled', true);
        
        if (isDispose) {
            button.html('<i class="fas fa-spinner fa-spin"></i> Disposing...');
        } else {
            button.html('<i class="fas fa-spinner fa-spin"></i> Archiving...');
        }
        
        const ajaxData = {
            _token:         '{{ csrf_token() }}',
            force_complete: isForceComplete,
            dispose:        isDispose
        };
        
        console.log('Sending AJAX with data:', ajaxData);
        console.log('URL:', `/case/${caseToProgress.id}/archive`);
        
        $.ajax({
            url: `/case/${caseToProgress.id}/archive`,
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
        button: button,
        action: 'complete'  // ← add this
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

$(document).on('click', '.execute-case-btn', function(e) {
    e.preventDefault();
    const button = $(this);
    const caseId = button.data('case-id');

    if (!caseId) {
        showAlert('error', 'Error: Could not identify case');
        return;
    }

    caseToProgress = {
        id:     caseId,
        button: button,
        action: 'execute'
    };

    // Style modal for Execute
    $('#modalHeader').removeClass('bg-success bg-warning').addClass('bg-primary text-white');
    $('#modalTitleText').text('Forward for Execution');
    $('#modalAlertBox').removeClass('alert-success alert-warning').addClass('alert-info');

    $('#stageProgressionMessage').html(`
        <strong>Forward this case to MALSU for execution?</strong><br>
        <small class="text-muted">The case will remain Active. MALSU will be notified to receive it.</small>
    `);
    $('#stageCaseInfo').text(`${button.data('case-no')} - ${button.data('establishment')}`);
    $('#stageCurrentStage').text(button.data('stage'));
    $('#stageNextStage').html('<span class="badge badge-primary"><i class="fas fa-gavel mr-1"></i>Forwarded to MALSU</span>');

    $('#confirmStageBtn')
        .removeClass('btn-success btn-warning')
        .addClass('btn-primary')
        .html('<i class="fas fa-paper-plane mr-2"></i>Forward to MALSU');

    // Show the delivery receipt fields
    $('#executeDeliverySection').show();

    // Clear previous values
    $('#execReceivedBy, #execDateReceived, #execTrackingNo').val('');
    $('#execCourier').val('');

    $('#stageProgressionModal').modal('show');
});

$('#stageProgressionModal').on('show.bs.modal', function() {
    if (!caseToProgress || caseToProgress.action !== 'execute') {
        $('#executeDeliverySection').hide();
    }
});

$('#stageProgressionModal').on('hidden.bs.modal', function() {
    $('#execReceivedBy').val('');
    $('#execDateReceived').val('');
    $('#execTrackingNo').val('');
    $('#execCourier').val('');
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


    if ($('#tabMALSU').hasClass('active')) {
    $('a[href="#tabMALSU"]').trigger('shown.bs.tab');
    }

    if ($('#tabSheriff').hasClass('active')) {
    $('a[href="#tabSheriff"]').trigger('shown.bs.tab');
    }

    // Double-click to edit cell
    $(document).on('dblclick', '.editable-cell', function(e) {
        e.preventDefault();
        
        // If already editing another cell, save it first
        if (currentEditingCell && currentEditingCell !== this) {
            saveCell($(currentEditingCell));
        }
        
        startEditing($(this));
    });

    $(document).on('dblclick', '.editable-cell small.address-subtext', function(e) {
        e.stopPropagation(); // prevent the parent cell's dblclick from firing too
        const $cell = $(this).closest('.editable-cell');
        if ($cell.find('input.address-inline-input').length) return;

        const currentAddress = $(this).text().trim();
        const $input = $('<input type="text" class="form-control form-control-sm address-inline-input">')
            .val(currentAddress)
            .css({ 'width': '100%', 'margin-top': '4px', 'border': '2px solid #4CAF50', 'box-shadow': '0 0 5px rgba(76,175,80,0.5)' });

        $(this).replaceWith($input);
        $input.focus().select();

        const saveAddress = () => {
            const newAddress = $input.val().trim();
            const $row = $cell.closest('tr');
            const recordId = $row.data('id');
            const nameText = $cell.find('span').first().text().trim();

            $input.replaceWith(`<small class="text-muted address-subtext" style="font-weight:normal;font-size:0.75rem;">${newAddress || currentAddress}</small>`);

            if (newAddress === currentAddress) return;

            $.ajax({
                url: '/case/' + recordId + '/inline-update',
                method: 'PUT',
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                data: { establishment_address: newAddress },
                success: function(response) {
                    if (response.success) {
                        showToast('Success', 'Address updated', 'success');
                    } else {
                        $cell.find('.address-subtext').text(currentAddress);
                        showToast('Error', response.message || 'Update failed', 'error');
                    }
                },
                error: function() {
                    $cell.find('.address-subtext').text(currentAddress);
                    showToast('Error', 'Failed to update address', 'error');
                }
            });
        };

        $input.on('keydown', function(e) {
            if (e.key === 'Enter') { e.preventDefault(); saveAddress(); }
            else if (e.key === 'Escape') { e.preventDefault(); $input.replaceWith(`<small class="text-muted address-subtext" style="font-weight:normal;font-size:0.75rem;">${currentAddress}</small>`); }
        });
        $input.on('blur', function() { setTimeout(saveAddress, 200); });
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
        const currentValue = $cell.find('span').length 
        ? $cell.find('span').first().text().trim() 
        : $cell.text().trim();
        
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
        else if (tableId === 'dataTableMALSU') tabKey = 'tabMALSU';
        else if (tableId === 'dataTableCM') tabKey = 'tabCM';
        
        const fieldConfig = tabConfigs[tabKey]?.fields[field];
        
        // Create input element based on field type
        let $input;
        
        if (fieldType === 'select' || fieldConfig?.type === 'select') {
            // Create select dropdown
            $input = $('<select class="form-control form-control-sm inline-edit-input"></select>');
            
            if (fieldConfig && fieldConfig.options) {
                fieldConfig.options.forEach(opt => {
                    const selected = opt.value == originalValue || opt.text == originalValue;
                    $input.append(
                        `<option value="${opt.value}" data-role="${opt.role || ''}" ${selected ? 'selected' : ''}>${opt.text}</option>`
                    );
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

        // ── Special handling: Sheriff Designate → confirm + transfer ──
        if (field === 'sheriff_designate') {
            const selectedRole = $input.find('option:selected').data('role') || '';
            const selectedName = newValue;

            // Cleared selection → just save blank, no transfer, no confirm needed
            if (!selectedName) {
                $.ajax({
                    url: `/malsu/${recordId}/inline-update`,
                    method: 'PUT',
                    data: { sheriff_designate: '' },
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    success: function() {
                        restoreCellDisplay($cell, '', fieldType);
                        showToast('Success', 'Sheriff designate cleared', 'success');
                    },
                    error: function() {
                        restoreCellDisplay($cell, originalValue, fieldType);
                        showToast('Error', 'Failed to clear', 'error');
                    },
                    complete: function() { currentEditingCell = null; }
                });
                return;
            }

            if (!selectedRole) {
                restoreCellDisplay($cell, originalValue, fieldType);
                currentEditingCell = null;
                showToast('Error', 'This sheriff has no assigned province role.', 'error');
                return;
            }

            Swal.fire({
                icon: 'question',
                title: 'Send case to sheriff?',
                html: `Send this case to <strong>${selectedName}</strong>?<br>
                    <small class="text-muted">It will leave your MALSU active cases until received.</small>`,
                showCancelButton: true,
                confirmButtonText: 'Yes, send it',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#28a745'
            }).then((result) => {
                if (result.isConfirmed) {
                    $cell.html('<i class="fas fa-spinner fa-spin text-primary"></i>');
                    $.ajax({
                        url: `/malsu/${recordId}/send-to-sheriff`,
                        method: 'PUT',
                        data: {
                            sheriff_name: selectedName,
                            target_role: selectedRole
                        },
                        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                        success: function(response) {
                            if (response.success) {
                                showToast('Success', response.message || 'Case sent successfully', 'success');
                                // Case leaves MALSU's active list immediately
                                $row.fadeOut(300, function() { $(this).remove(); });
                            } else {
                                restoreCellDisplay($cell, originalValue, fieldType);
                                showToast('Error', response.message || 'Send failed', 'error');
                            }
                        },
                        error: function(xhr) {
                            restoreCellDisplay($cell, originalValue, fieldType);
                            showToast('Error', xhr.responseJSON?.message || 'Failed to send case', 'error');
                        },
                        complete: function() { currentEditingCell = null; }
                    });
                } else {
                    // Cancelled → wipe the value entirely, per your spec
                    restoreCellDisplay($cell, '', fieldType);
                    currentEditingCell = null;
                }
            });
            return; // stop here — skip the generic save logic below for this field
        }
        
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
        else if (tableId === 'dataTableMALSU') endpoint = '/malsu/';
        else if (tableId === 'dataTableCM') endpoint = '/case/';
        
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
                    updateRowWithComputedFields($row, response.data, fieldType);

                    // Restore address subtext if this was the establishment_name cell
                    if (field === 'establishment_name') {
                        const address = response.data.establishment_address || '';
                        const addressHtml = address
                            ? `<br><small class="text-muted address-subtext" style="font-weight:normal;font-size:0.75rem;">${address}</small>`
                            : '';
                        $cell.html(`<span>${response.data.establishment_name || '-'}</span>${addressHtml}`);
                        $cell.attr('data-address', address);
                    }

                    $cell.addClass('bg-success text-white');
                    setTimeout(() => {
                        $cell.removeClass('bg-success text-white');
                    }, 1000);

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
        // Special case: establishment_name needs to keep address subtext
        if ($cell.data('field') === 'establishment_name') {
            const address = $cell.data('address') || '';
            const addressHtml = address
                ? `<br><small class="text-muted address-subtext" style="font-weight:normal;font-size:0.75rem;">${address}</small>`
                : '';
            $cell.html(`<span>${value || '-'}</span>${addressHtml}`);
            return;
        }

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
        
        // This is at the END of restoreCellDisplay, the final html assignment
        if ($cell.data('field') === 'establishment_name') {
            // Don't overwrite address subtext on cancel — just update the name span
            $cell.find('span').first().text(displayValue);
            if (!$cell.find('small.address-subtext').length) {
                $cell.html(displayValue); // fallback if no subtext exists
            }
        } else {
            $cell.html(displayValue);
        }
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
                'type_of_industry': { type: 'text' },
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
                'status_po_pct': {
                    type: 'select',
                    options: [
                        { value: '', text: 'Select' },
                        { value: 'Within', text: 'Within' },
                        { value: 'Beyond', text: 'Beyond' }
                    ]
                },
                'status_pct': {
                    type: 'select',
                    options: [
                        { value: '', text: 'Select' },
                        { value: 'Within', text: 'Within' },
                        { value: 'Beyond', text: 'Beyond' }
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

    tabConfigs['tabMALSU'] = {
        name: 'malsu',
        endpoint: '/malsu/',
        editBtnClass: '.edit-row-btn-case',
        saveBtnClass: '.save-btn-case',
        cancelBtnClass: '.cancel-btn-case',
        alertPrefix: 'tabMALSU',
        fields: {
            'regional_docket_number':                   { type: 'text' },
            'sheriff_designate': {
                type: 'select',
                options: [
                    { value: '', text: 'Select Sheriff', role: '' },
                    { value: 'Juan Dela Cruz', text: 'Juan Dela Cruz', role: 'sheriff_albay' },
                    { value: 'Maria Santos',  text: 'Maria Santos',  role: 'sheriff_camarines_sur' },
                    { value: 'PLACEHOLDER_1', text: 'PLACEHOLDER_1', role: 'sheriff_camarines_norte' }, // ← replace
                    { value: 'PLACEHOLDER_2', text: 'PLACEHOLDER_2', role: 'sheriff_catanduanes' },      // ← replace
                    { value: 'PLACEHOLDER_3', text: 'PLACEHOLDER_3', role: 'sheriff_masbate' },          // ← replace
                    { value: 'PLACEHOLDER_4', text: 'PLACEHOLDER_4', role: 'sheriff_sorsogon' }          // ← replace
                ]
            },
            'date_compliance_order':                    { type: 'date' },
            'total_gls_monetary_award':                 { type: 'number', step: '0.01' },
            'total_workers_benefited':                  { type: 'number' },
            'amount_penalty_double_indemnity':          { type: 'number', step: '0.01' },
            'voluntary_compliance':                     { type: 'text' },
            'action_taken':                             { type: 'text' },
            'total_gls_monetary_satisfied':             { type: 'number', step: '0.01' },
            'total_workers_satisfied':                  { type: 'number' },
            'complied_oshs_violations':                 { type: 'text' },
            'total_penalty_double_indemnity_collected': { type: 'number', step: '0.01' },
            'total_oshs_penalty_admin_fines_collected': { type: 'number', step: '0.01' },
            'total_workers_absorbed':                   { type: 'number' },
            'full_or_partial':                          { type: 'text' },
            'date_writ_of_execution_served':            { type: 'date' },
            'date_indorsed_to_po':                      { type: 'date' },
            'po_date_received':                         { type: 'date' },
            'ro_received_sheriffs_return':              { type: 'date' },
            'case_tag': {                          // ← ADD THIS
                type: 'select',
                options: [
                    { value: '',                        text: '— No Tag —' },
                    { value: 'For Execution',            text: 'For Execution' },
                    { value: 'Motion for Reconsideration', text: 'Motion for Reconsideration' }
                ]
            }
        }
    };
    tabConfigs['tabCM']    = tabConfigs['tab0'];
    tabConfigs['tabProv-albay']          = tabConfigs['tab0'];
    tabConfigs['tabProv-camarines_sur']  = tabConfigs['tab0'];
    tabConfigs['tabProv-camarines_norte']= tabConfigs['tab0'];
    tabConfigs['tabProv-catanduanes']    = tabConfigs['tab0'];
    tabConfigs['tabProv-masbate']        = tabConfigs['tab0'];
    tabConfigs['tabProv-sorsogon']       = tabConfigs['tab0'];

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

        // ── DEBUG LOGS ──
        console.log('=== SAVE BUTTON CLICKED ===');
        console.log('currentTab:', currentTab);
        console.log('config.name:', config.name);
        console.log('config.endpoint:', config.endpoint);
        console.log('recordId:', recordId);
        console.log('Full URL will be:', `${config.endpoint}${recordId}/inline-update`);
        console.log('===========================');
        // ── END DEBUG ──

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

            if (field === 'establishment_name') {
                originalData['establishment_name'] = cell.find('span').first().text().trim();
                originalData['establishment_address'] = cell.data('address') || '';
            } else {
                originalData[field] = cell.text().trim();
            }

            const input = createInput(field, cell, config);
            cell.html(input);
            cell.addClass('edit-mode');
        });

        // ── ADD THIS: show case_tag select and hide badge in edit mode ──
        if (config.name === 'malsu') {
            const $badge  = row.find('.case-tag-badge');
            const $select = row.find('.case-tag-select');
            $badge.hide();
            $select.addClass('edit-input').show();
        }
        // ── END ADD ──

        const buttonSuffix = config.name === 'case' ? '-case' :
                            config.name === 'malsu' ? '-case' :
                            config.name === 'docketing' ? '-docketing' :
                            config.name === 'hearing' ? '-hearing' :
                            config.name === 'review-and-drafting' ? '-review' :
                            config.name === 'orders-and-disposition' ? '-orders' :
                            config.name === 'compliance-and-awards' ? '-compliance' :
                            config.name === 'appeals-and-resolution' ? '-appeals' : '';

        const $actionsCell = row.find('.actions-cell');
        $actionsCell.data('was-expanded', $actionsCell.hasClass('expanded'));
        $actionsCell.removeClass('expanded').addClass('collapsed edit-mode-cell');

        const $container = $actionsCell.find('.action-buttons-container');
        $container.data('original-html', $container.html());
        $container.html(`
            <button class="action-toggle-btn save-mode save-btn${buttonSuffix}" title="Save changes">
                <i class="fas fa-check"></i>
            </button>
            <button class="action-toggle-btn exit-mode cancel-btn${buttonSuffix}" title="Cancel editing" style="margin-left:4px;">
                <i class="fas fa-times"></i>
            </button>
        `);

        row.find('.edit-input').first().focus();
    }

    function createInput(field, cell, config) {
        const fieldConfig = config.fields[field];
        const currentValue = (() => {
            if (field === 'establishment_name') {
                const nameText = cell.find('span').first().text().trim();
                return nameText === '-' ? '' : nameText;
            }
            return cell.text().trim() === '-' ? '' : cell.text().trim();
        })();
        
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
                const addressValue = cell.data('address') || '';
                return `
                    <input type="text" 
                        class="form-control form-control-sm edit-input" 
                        value="${inputValue}" 
                        data-field="establishment_name"
                        placeholder="Establishment name"
                        style="margin-bottom: 3px;">
                    <input type="text" 
                        class="form-control form-control-sm edit-input address-edit-input" 
                        value="${addressValue}" 
                        data-field="establishment_address"
                        placeholder="Address"
                        style="font-size: 0.75rem; padding: 2px 6px; height: auto; color: #6c757d;">
                `;
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

        const $caseTagSelect = row.find('.case-tag-select');
        if ($caseTagSelect.length) {
            updatedData['case_tag'] = $caseTagSelect.val() || '';
        }

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

                    // Refresh case_tag badge (MALSU tab only)
                    if (response.case_tag !== undefined) {
                        const tagColors = {
                            'For Execution':              'danger',
                            'Motion for Reconsideration': 'warning'
                        };
                        const tag    = response.case_tag || '';
                        const color  = tagColors[tag] || 'secondary';
                        const $badge = row.find('.case-tag-badge');

                        if (tag) {
                            $badge
                                .removeClass('badge-danger badge-warning badge-secondary')
                                .addClass('badge-' + color)
                                .attr('data-tag', tag)
                                .html('<i class="fas fa-bolt mr-1"></i>' + tag.toUpperCase())
                                .show();
                        } else {
                            $badge.hide().attr('data-tag', '');
                        }
                    }

                    showToast('Success', 'Updated successfully', 'success');
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

            if (field === 'establishment_name') {
                const address = responseData.establishment_address || '';
                const addressHtml = address 
                    ? `<br><small class="text-muted address-subtext" style="font-weight:normal;font-size:0.75rem;">${address}</small>` 
                    : '';
                cell.html(`<span>${displayValue !== '-' ? displayValue : '-'}</span>${addressHtml}`);
                cell.attr('data-address', address);
                cell.removeClass('edit-mode');
                return;
            }

            // For all other fields: write the display value and clear edit mode
            cell.html(displayValue);
            cell.removeClass('edit-mode');
        });

        if (config.name === 'malsu') {
            const $select = row.find('.case-tag-select');
            const $badge  = row.find('.case-tag-badge');
            const tag     = $select.val() || '';

            const tagColors = {
                'For Execution':              'danger',
                'Motion for Reconsideration': 'warning'
            };

            $select.removeClass('edit-input').hide();

            if (tag) {
                const color = tagColors[tag] || 'secondary';
                $badge
                    .removeClass('badge-danger badge-warning badge-secondary')
                    .addClass('badge-' + color)
                    .attr('data-tag', tag)
                    .html('<i class="fas fa-bolt mr-1"></i>' + tag.toUpperCase())
                    .show();
            } else {
                $badge.attr('data-tag', '').hide();
            }
        }
    }

    function restoreButtons(saveBtn, cancelBtn, originalContent) {
        saveBtn.html(originalContent).prop('disabled', false);
        cancelBtn.prop('disabled', false);
    }

    function restoreActionButtons(row) {
        const $actionsCell = row.find('.actions-cell');
        const $container = $actionsCell.find('.action-buttons-container');

        // Restore original toggle + action buttons HTML
        $container.html($container.data('original-html'));

        // Remove edit-mode-cell, restore to collapsed
        $actionsCell.removeClass('edit-mode-cell expanded').addClass('collapsed');

        // Re-icon the toggle button back to chevron-right
        $actionsCell.find('.action-toggle-btn i')
            .removeClass('fa-chevron-left fa-check fa-times')
            .addClass('fa-chevron-right');
    }

    function cancelEdit() {
        if (!currentEditingRow) return;

        currentEditingRow.find('.editable-cell:not(.readonly-cell)').each(function() {
            const cell = $(this);
            const field = cell.data('field');
            let displayValue = originalData[field] || '';

            if (field === 'current_stage' && displayValue.includes(': ')) {
                displayValue = displayValue.split(': ')[1];
            }

            if (field === 'establishment_name') {
                const originalAddress = originalData['establishment_address'] || '';
                const addressHtml = originalAddress 
                    ? `<br><small class="text-muted address-subtext" style="font-weight:normal;font-size:0.75rem;">${originalAddress}</small>` 
                    : '';
                cell.html(`<span>${displayValue || '-'}</span>${addressHtml}`);
                cell.attr('data-address', originalAddress);
                cell.removeClass('edit-mode');
                return;
            }

            cell.html(displayValue);
            cell.removeClass('edit-mode');
        });

        restoreActionButtons(currentEditingRow);
        resetEditState();

         if (config.name === 'malsu') {
            const $badge  = currentEditingRow.find('.case-tag-badge');
            const $select = currentEditingRow.find('.case-tag-select');
            $select.removeClass('edit-input').hide();
            if ($badge.attr('data-tag')) {
                $badge.show();
            }
        }
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

// document.addEventListener('DOMContentLoaded', function() {
//     const els = {
//         'body':              document.body,
//         '#wrapper':          document.getElementById('wrapper'),
//         '#content-wrapper':  document.getElementById('content-wrapper'),
//         '#content':          document.getElementById('content'),
//         '.container-fluid':  document.querySelector('#content .container-fluid'),
//         '#dataTableTabsContent': document.getElementById('dataTableTabsContent'),
//         '#tab0':             document.getElementById('tab0'),
//         '.card':             document.querySelector('#tab0 .card'),
//         '.card-body':        document.querySelector('#tab0 .card-body'),
//         '.table-container': document.querySelector('.table-container'),
// '.dataTables_wrapper': document.querySelector('.dataTables_wrapper'),
// '.dataTables_paginate': document.querySelector('.dataTables_paginate'),
//     };

//     let output = '<div style="position:fixed;bottom:0;left:0;right:0;background:#000;color:#0f0;font-family:monospace;font-size:12px;padding:10px;z-index:99999;max-height:200px;overflow:auto;">';
//     output += '<strong>HEIGHT DEBUG:</strong><br>';
    
//     for (const [name, el] of Object.entries(els)) {
//         if (el) {
//             const rect = el.getBoundingClientRect();
//             const computed = window.getComputedStyle(el);
//             output += `<span style="color:yellow">${name}</span>: 
//                 height=${Math.round(rect.height)}px | 
//                 bottom=${Math.round(rect.bottom)}px | 
//                 padding-bottom=${computed.paddingBottom} | 
//                 margin-bottom=${computed.marginBottom}<br>`;
//         }
//     }
//     output += '</div>';
//     document.body.insertAdjacentHTML('beforeend', output);
// });

</script>
@endpush