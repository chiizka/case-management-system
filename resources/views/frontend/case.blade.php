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
    font-size: 0.75rem;
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
            <li class="nav-item">
                <a class="nav-link" id="tab1-tab" data-toggle="tab" href="#tab1" role="tab" aria-controls="tab1" aria-selected="false">
                    Inspection
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="tab2-tab" data-toggle="tab" href="#tab2" role="tab" aria-controls="tab2" aria-selected="false">
                    Docketing
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="tab3-tab" data-toggle="tab" href="#tab3" role="tab" aria-controls="tab3" aria-selected="false">
                    Hearing Process
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="tab4-tab" data-toggle="tab" href="#tab4" role="tab" aria-controls="tab4" aria-selected="false">
                    Review & Drafting
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="tab5-tab" data-toggle="tab" href="#tab5" role="tab" aria-controls="tab5" aria-selected="false">
                    Orders & Disposition
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="tab6-tab" data-toggle="tab" href="#tab6" role="tab" aria-controls="tab6" aria-selected="false">
                    Compliance & Awards
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="tab7-tab" data-toggle="tab" href="#tab7" role="tab" aria-controls="tab7" aria-selected="false">
                    Appeals & Resolution
                </a>
            </li>
        </ul>

        <!-- Tabs Content -->
        <div class="tab-content mt-3" id="dataTableTabsContent">
            
<!-- Tab 0: All Active Cases -->
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
                                        <th>Inspection ID</th>
                                        <th>Case No.</th>
                                        <th>Establishment Name</th>
                                        <th>Current Stage</th>
                                        <th>Overall Status</th>
                                        <th>Created At</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if(isset($cases) && $cases->count() > 0)
                                        @foreach($cases as $case)
                                            <tr data-id="{{ $case->id }}">
                                                <td class="editable-cell" data-field="inspection_id">{{ $case->inspection_id ?? '-' }}</td>
                                                <td class="editable-cell" data-field="case_no">{{ $case->case_no ?? '-' }}</td>
                                                <td class="editable-cell" data-field="establishment_name" title="{{ $case->establishment_name ?? '' }}">
                                                    {{ $case->establishment_name ? Str::limit($case->establishment_name, 25) : '-' }}
                                                </td>
                                                <td class="editable-cell" data-field="current_stage" data-type="select">{{ explode(': ', $case->current_stage)[1] ?? $case->current_stage ?? '-' }}</td>
                                                <td class="editable-cell" data-field="overall_status" data-type="select">{{ $case->overall_status ?? '-' }}</td>
                                                <td class="non-editable">{{ $case->created_at ? \Carbon\Carbon::parse($case->created_at)->format('Y-m-d') : '-' }}</td>
                                                <td>
                                                    <button class="btn btn-warning btn-sm edit-row-btn-case" title="Edit Row">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <form action="/case/{{ $case->id }}" method="POST" style="display:inline;">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger btn-sm delete-btn" data-case-id="{{ $case->id }}" onclick="return confirm('Delete this case?')" title="Delete">
                                                            <i class="fas fa-trash"></i>  
                                                        </button>
                                                    </form>
                                                    <button class="btn btn-info btn-sm" title="View">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="7" class="text-center">No cases found. Click "Add Case" to create your first case.</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab 1: Inspection -->
            <div class="tab-pane fade" id="tab1" role="tabpanel" aria-labelledby="tab1-tab">
                <div class="card shadow mb-4">
                    <div class="card-body">
                        <!-- Success Message -->
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('success') }}
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        @endif
                        <!-- Error Message -->
                        @if(session('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                {{ session('error') }}
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        @endif

                        <!-- Success/Error alerts for AJAX -->
                        <div class="alert alert-success alert-dismissible fade" role="alert" id="success-alert" style="display: none;">
                            <span id="success-message"></span>
                            <button type="button" class="close" onclick="hideAlert('success-alert')">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        
                        <div class="alert alert-danger alert-dismissible fade" role="alert" id="error-alert" style="display: none;">
                            <span id="error-message"></span>
                            <button type="button" class="close" onclick="hideAlert('error-alert')">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>

                        <!-- Search + Buttons Row -->
                        <div class="d-flex justify-content-between align-items-center mb-3 custom-search-container">
                            <div class="d-flex align-items-center">
                                <label class="mr-2 mb-0" style="font-size: 0.8rem;">Search:</label>
                                <input type="search" class="form-control form-control-sm" id="customSearch1" placeholder="Search inspections..." style="width: 200px;">
                            </div>
                        </div>

                        <!-- Table Container -->
                        <div class="table-container">
                            <table class="table table-bordered compact-table sticky-table" id="dataTable1" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>Inspection ID</th>
                                        <th>Name of Establishment</th>
                                        <th>PO Office</th>
                                        <th>Inspector Name</th>
                                        <th>Inspector Authority No</th>
                                        <th>Date of Inspection</th>
                                        <th>Date of NR</th>
                                        <th>Lapse 20 Day Period</th>
                                        <th>TWG ALI</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if(isset($inspections) && $inspections->count() > 0)
                                        @foreach($inspections as $inspection)
                                            <tr data-id="{{ $inspection->id }}">
                                                <td class="editable-cell" data-field="inspection_id">{{ $inspection->case->inspection_id ?? '-' }}</td>
                                                <td class="editable-cell" data-field="establishment_name" title="{{ $inspection->case->establishment_name ?? '' }}">
                                                    {{ $inspection->case ? Str::limit($inspection->case->establishment_name, 25) : '-' }}
                                                </td>
                                                <td class="editable-cell" data-field="po_office">{{ $inspection->po_office ?? '-' }}</td>
                                                <td class="editable-cell" data-field="inspector_name">{{ $inspection->inspector_name ?? '-' }}</td>
                                                <td class="editable-cell" data-field="inspector_authority_no">{{ $inspection->inspector_authority_no ?? '-' }}</td>
                                                <td class="editable-cell" data-field="date_of_inspection" data-type="date">{{ $inspection->date_of_inspection ? \Carbon\Carbon::parse($inspection->date_of_inspection)->format('Y-m-d') : '-' }}</td>
                                                <td class="editable-cell" data-field="date_of_nr" data-type="date">{{ $inspection->date_of_nr ? \Carbon\Carbon::parse($inspection->date_of_nr)->format('Y-m-d') : '-' }}</td>
                                                <td class="editable-cell readonly-cell" data-field="lapse_20_day_period" data-type="date" title="Auto-calculated: 20 days after Date of NR">
                                                    {{ $inspection->lapse_20_day_period ? \Carbon\Carbon::parse($inspection->lapse_20_day_period)->format('Y-m-d') : '-' }}
                                                </td>
                                                <td class="editable-cell" data-field="twg_ali">{{ $inspection->twg_ali ?? '-' }}</td>
                                                <td>
                                                    <a href="{{ route('inspection.show', $inspection->id) }}" class="btn btn-info btn-sm" title="View">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <button class="btn btn-warning btn-sm edit-row-btn" title="Edit Row">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <form action="{{ route('inspection.destroy', $inspection->id) }}" method="POST" style="display:inline;">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger btn-sm inspection-delete-btn" title="Delete" onclick="return confirm('Are you sure you want to delete this inspection?')">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                    
                                                    @if($inspection->case && $inspection->case->current_stage === '1: Inspections')
                                                        <form action="{{ route('case.nextStage', $inspection->case->id) }}" method="POST" style="display:inline;">
                                                            @csrf
                                                            <button type="submit" class="btn btn-success btn-sm ml-1" title="Move to Docketing" onclick="return confirm('Complete inspection and move to Docketing?')">
                                                                <i class="fas fa-arrow-right"></i> Next
                                                            </button>
                                                        </form>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="10" class="text-center">No inspections found. Click "Add Inspection" to create your first inspection.</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab 2: Docketing -->
            <div class="tab-pane fade" id="tab2" role="tabpanel" aria-labelledby="tab2-tab">
                <div class="card shadow mb-4">
                    <div class="card-body">
                        <!-- Success/Error alerts for AJAX -->
                        <div class="alert alert-success alert-dismissible fade" role="alert" id="success-alert-tab2" style="display: none;">
                            <span id="success-message-tab2"></span>
                            <button type="button" class="close" onclick="hideAlert('success-alert-tab2')">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        
                        <div class="alert alert-danger alert-dismissible fade" role="alert" id="error-alert-tab2" style="display: none;">
                            <span id="error-message-tab2"></span>
                            <button type="button" class="close" onclick="hideAlert('error-alert-tab2')">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>

                        <!-- Search + Buttons Row -->
                        <div class="d-flex justify-content-between align-items-center mb-3 custom-search-container">
                            <div class="d-flex align-items-center">
                                <label class="mr-2 mb-0" style="font-size: 0.8rem;">Search:</label>
                                <input type="search" class="form-control form-control-sm" id="customSearch2" placeholder="Search docketing records..." style="width: 200px;">
                            </div>
                        </div>

                        <!-- Table Container -->
                        <div class="table-container">
                            <table class="table table-bordered compact-table sticky-table" id="dataTable2" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>Inspection ID</th>
                                        <th>Name of Establishment</th>
                                        <th>PCT for Docketing</th>
                                        <th>Date Scheduled/Docketed</th>
                                        <th>Aging (Docket)</th>
                                        <th>Status (Docket)</th>
                                        <th>Hearing Officer (MIS)</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if(isset($docketing) && $docketing->count() > 0)
                                        @foreach($docketing as $record)
                                            <tr data-id="{{ $record->id }}">
                                                <td class="editable-cell readonly-cell" data-field="inspection_id" title="From case record">{{ $record->case->inspection_id ?? '-' }}</td>
                                                <td class="editable-cell readonly-cell" data-field="establishment_name" title="{{ $record->case->establishment_name ?? '' }}">
                                                    {{ $record->case ? Str::limit($record->case->establishment_name, 25) : '-' }}
                                                </td>
                                                <td class="editable-cell" data-field="pct_for_docketing">{{ $record->pct_for_docketing ?? '-' }}</td>
                                                <td class="editable-cell" data-field="date_scheduled_docketed" data-type="date">{{ $record->date_scheduled_docketed ? \Carbon\Carbon::parse($record->date_scheduled_docketed)->format('Y-m-d') : '-' }}</td>
                                                <td class="editable-cell" data-field="aging_docket">{{ $record->aging_docket ?? '-' }}</td>
                                                <td class="editable-cell" data-field="status_docket">
                                                    <span class="badge badge-{{ $record->status_docket == 'Pending' ? 'warning' : ($record->status_docket == 'Completed' ? 'success' : 'secondary') }}">
                                                        {{ $record->status_docket ?? 'Pending' }}
                                                    </span>
                                                </td>
                                                <td class="editable-cell" data-field="hearing_officer_mis">{{ $record->hearing_officer_mis ?? '-' }}</td>
                                                <td>
                                                    <a href="{{ route('docketing.show', $record->id) }}" class="btn btn-info btn-sm" title="View">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <button class="btn btn-warning btn-sm edit-row-btn-docketing" title="Edit Row">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <form action="{{ route('docketing.destroy', $record->id) }}" method="POST" style="display:inline;">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger btn-sm docketing-delete-btn" title="Delete" onclick="return confirm('Are you sure you want to delete this docketing record?')">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                    
                                                    @if($record->case && $record->case->current_stage === '2: Docketing')
                                                        <form action="{{ route('case.nextStage', $record->case->id) }}" method="POST" style="display:inline;">
                                                            @csrf
                                                            <button type="submit" class="btn btn-success btn-sm ml-1" title="Move to Hearing" onclick="return confirm('Complete docketing and move to Hearing?')">
                                                                <i class="fas fa-arrow-right"></i> Next
                                                            </button>
                                                        </form>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="8" class="text-center">No docketing records found.</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab 3: Hearing Process -->
            <div class="tab-pane fade" id="tab3" role="tabpanel" aria-labelledby="tab3-tab">
                <div class="card shadow mb-4">
                    <div class="card-body">
                        <!-- Search + Buttons Row -->
                        <div class="d-flex justify-content-between align-items-center mb-3 custom-search-container">
                            <div class="d-flex align-items-center">
                                <label class="mr-2 mb-0" style="font-size: 0.8rem;">Search:</label>
                                <input type="search" class="form-control form-control-sm" id="customSearch3" placeholder="Search hearing records..." style="width: 200px;">
                            </div>
                        </div>

                        <!-- Table Container -->
                        <div class="table-container">
                            <table class="table table-bordered compact-table sticky-table" id="dataTable3" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>Inspection ID</th>
                                        <th>Name of Establishment</th>
                                        <th>Date of 1st MC (Actual)</th>
                                        <th>1st MC PCT</th>
                                        <th>Status (1st MC)</th>
                                        <th>Date of 2nd/Last MC (Actual)</th>
                                        <th>2nd/Last MC PCT</th>
                                        <th>Status (2nd MC)</th>
                                        <th>Case Folder forwarded to RO</th>
                                        <th>Complete case folder? (Y/N)</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if(isset($hearingProcess) && $hearingProcess->count() > 0)
                                        @foreach($hearingProcess as $record)
                                            <tr>
                                                <td>{{ $record->case->inspection_id ?? '-' }}</td>
                                                <td title="{{ $record->case->establishment_name ?? '' }}">
                                                    {{ $record->case ? Str::limit($record->case->establishment_name, 25) : '-' }}
                                                </td>
                                                <td>{{ $record->date_1st_mc_actual ? \Carbon\Carbon::parse($record->date_1st_mc_actual)->format('Y-m-d') : '-' }}</td>
                                                <td>{{ $record->first_mc_pct ?? '-' }}</td>
                                                <td>
                                                    <span class="badge badge-{{ $record->status_1st_mc == 'Completed' ? 'success' : ($record->status_1st_mc == 'Ongoing' ? 'warning' : 'secondary') }}">
                                                        {{ $record->status_1st_mc ?? 'Pending' }}
                                                    </span>
                                                </td>
                                                <td>{{ $record->date_2nd_last_mc ? \Carbon\Carbon::parse($record->date_2nd_last_mc)->format('Y-m-d') : '-' }}</td>
                                                <td>{{ $record->second_last_mc_pct ?? '-' }}</td>
                                                <td>
                                                    <span class="badge badge-{{ $record->status_2nd_mc == 'Completed' ? 'success' : ($record->status_2nd_mc == 'In Progress' ? 'warning' : 'secondary') }}">
                                                        {{ $record->status_2nd_mc ?? 'Pending' }}
                                                    </span>
                                                </td>
                                                <td>{{ $record->case_folder_forwarded_to_ro ?? '-' }}</td>
                                                <td>
                                                    <span class="badge badge-{{ $record->complete_case_folder == 'Y' ? 'success' : 'warning' }}">
                                                        {{ $record->complete_case_folder ?? 'N' }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="{{ route('hearing-process.show', $record->id) }}" class="btn btn-info btn-sm" title="View">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('hearing-process.edit', $record->id) }}" class="btn btn-warning btn-sm" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form action="{{ route('hearing-process.destroy', $record->id) }}" method="POST" style="display:inline;">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger btn-sm delete-btn" title="Delete" onclick="return confirm('Are you sure you want to delete this hearing process record?')">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="11" class="text-center">No hearing process records found.</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab 4: Review & Drafting -->
            <div class="tab-pane fade" id="tab4" role="tabpanel" aria-labelledby="tab4-tab">
                <div class="card shadow mb-4">
                    <div class="card-body">
                        <!-- Search + Buttons Row -->
                        <div class="d-flex justify-content-between align-items-center mb-3 custom-search-container">
                            <div class="d-flex align-items-center">
                                <label class="mr-2 mb-0" style="font-size: 0.8rem;">Search:</label>
                                <input type="search" class="form-control form-control-sm" id="customSearch4" placeholder="Search review records..." style="width: 200px;">
                            </div>
                        </div>

                        <!-- Table Container -->
                        <div class="table-container">
                            <table class="table table-bordered compact-table sticky-table" id="dataTable4" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>Inspection ID</th>
                                        <th>Name of Establishment</th>
                                        <th>Draft Order from PO (Type of Order)</th>
                                        <th>Applicable Draft Order? (Y/N)</th>
                                        <th>PO PCT (45 days from lapse)</th>
                                        <th>Aging (PO PCT)</th>
                                        <th>Status (PO PCT)</th>
                                        <th>Date Received from PO</th>
                                        <th>Reviewer/Drafter</th>
                                        <th>Date Received by Reviewer/Drafter</th>
                                        <th>Date returned from Drafter to Case Mngt</th>
                                        <th>Aging (10 days for TSSD Reviewer)</th>
                                        <th>Status (Reviewer/Drafter)</th>
                                        <th>Draft Order of TSSD Reviewer/Drafter</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if(isset($reviewAndDrafting) && $reviewAndDrafting->count() > 0)
                                        @foreach($reviewAndDrafting as $record)
                                            <tr>
                                                <td>{{ $record->case->inspection_id ?? '-' }}</td>
                                                <td title="{{ $record->case->establishment_name ?? '' }}">
                                                    {{ $record->case ? Str::limit($record->case->establishment_name, 25) : '-' }}
                                                </td>
                                                <td>{{ $record->draft_order_type ?? '-' }}</td>
                                                <td>
                                                    <span class="badge badge-{{ $record->applicable_draft_order == 'Y' ? 'success' : 'warning' }}">
                                                        {{ $record->applicable_draft_order ?? 'N' }}
                                                    </span>
                                                </td>
                                                <td>{{ $record->po_pct ?? '-' }}</td>
                                                <td>{{ $record->aging_po_pct ?? '-' }}</td>
                                                <td>
                                                    <span class="badge badge-{{ $record->status_po_pct == 'Completed' ? 'success' : ($record->status_po_pct == 'In Progress' ? 'warning' : 'secondary') }}">
                                                        {{ $record->status_po_pct ?? 'Pending' }}
                                                    </span>
                                                </td>
                                                <td>{{ $record->date_received_from_po ? \Carbon\Carbon::parse($record->date_received_from_po)->format('Y-m-d') : '-' }}</td>
                                                <td>{{ $record->reviewer_drafter ?? '-' }}</td>
                                                <td>{{ $record->date_received_by_reviewer ? \Carbon\Carbon::parse($record->date_received_by_reviewer)->format('Y-m-d') : '-' }}</td>
                                                <td>{{ $record->date_returned_from_drafter ? \Carbon\Carbon::parse($record->date_returned_from_drafter)->format('Y-m-d') : '-' }}</td>
                                                <td>{{ $record->aging_10_days_tssd ?? '-' }}</td>
                                                <td>
                                                    <span class="badge badge-{{ 
                                                        $record->status_reviewer_drafter == 'Completed' ? 'success' : 
                                                        ($record->status_reviewer_drafter == 'Ongoing' ? 'warning' : 
                                                        ($record->status_reviewer_drafter == 'Approved' ? 'primary' : 
                                                        ($record->status_reviewer_drafter == 'Returned' ? 'info' : 
                                                        ($record->status_reviewer_drafter == 'Overdue' ? 'danger' : 'secondary')))) 
                                                    }}">
                                                        {{ $record->status_reviewer_drafter ?? 'Pending' }}
                                                    </span>
                                                </td>
                                                <td>{{ $record->draft_order_tssd_reviewer ?? '-' }}</td>
                                                <td>
                                                    <a href="{{ route('review-and-drafting.show', $record->id) }}" class="btn btn-info btn-sm" title="View">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('review-and-drafting.edit', $record->id) }}" class="btn btn-warning btn-sm" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form action="{{ route('review-and-drafting.destroy', $record->id) }}" method="POST" style="display:inline;">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger btn-sm delete-btn" title="Delete" onclick="return confirm('Are you sure you want to delete this review & drafting record?')">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
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
                    </div>
                </div>
            </div>

            <!-- Tab 5: Orders & Disposition -->
            <div class="tab-pane fade" id="tab5" role="tabpanel" aria-labelledby="tab5-tab">
                <div class="card shadow mb-4">
                    <div class="card-body">
                        <!-- Search + Buttons Row -->
                        <div class="d-flex justify-content-between align-items-center mb-3 custom-search-container">
                            <div class="d-flex align-items-center">
                                <label class="mr-2 mb-0" style="font-size: 0.8rem;">Search:</label>
                                <input type="search" class="form-control form-control-sm" id="customSearch5" placeholder="Search orders..." style="width: 200px;">
                            </div>
                        </div>

                        <!-- Table Container -->
                        <div class="table-container">
                            <table class="table table-bordered compact-table sticky-table" id="dataTable5" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>Inspection ID</th>
                                        <th>Name of Establishment</th>
                                        <th>Aging (2 days for Finalization)</th>
                                        <th>Status (Finalization)</th>
                                        <th>PCT (96 days from NR)</th>
                                        <th>Date Signed (MIS)</th>
                                        <th>Status (PCT)</th>
                                        <th>Reference Date (PCT)</th>
                                        <th>Aging (PCT)</th>
                                        <th>Disposition (MIS)</th>
                                        <th>Disposition (Actual)</th>
                                        <th>Findings to be Complied</th>
                                        <th>Date of Order (Actual)</th>
                                        <th>Released Date (Actual)</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if(isset($ordersAndDisposition) && $ordersAndDisposition->count() > 0)
                                        @foreach($ordersAndDisposition as $record)
                                            <tr>
                                                <td>{{ $record->case->inspection_id ?? '-' }}</td>
                                                <td title="{{ $record->case->establishment_name ?? '' }}">
                                                    {{ $record->case ? Str::limit($record->case->establishment_name, 25) : '-' }}
                                                </td>
                                                <td>{{ $record->aging_2_days_finalization ?? '-' }}</td>
                                                <td>
                                                    <span class="badge badge-{{ $record->status_finalization == 'Completed' ? 'success' : ($record->status_finalization == 'Pending' ? 'warning' : 'secondary') }}">
                                                        {{ $record->status_finalization ?? 'Pending' }}
                                                    </span>
                                                </td>
                                                <td>{{ $record->pct_96_days ?? '-' }}</td>
                                                <td>{{ $record->date_signed_mis ? \Carbon\Carbon::parse($record->date_signed_mis)->format('Y-m-d') : '-' }}</td>
                                                <td>
                                                    <span class="badge badge-{{ $record->status_pct == 'Completed' ? 'success' : ($record->status_pct == 'Ongoing' ? 'warning' : 'secondary') }}">
                                                        {{ $record->status_pct ?? 'Pending' }}
                                                    </span>
                                                </td>
                                                <td>{{ $record->reference_date_pct ? \Carbon\Carbon::parse($record->reference_date_pct)->format('Y-m-d') : '-' }}</td>
                                                <td>{{ $record->aging_pct ?? '-' }}</td>
                                                <td>{{ $record->disposition_mis ?? '-' }}</td>
                                                <td>{{ $record->disposition_actual ?? '-' }}</td>
                                                <td>{{ Str::limit($record->findings_to_comply, 40) ?? '-' }}</td>
                                                <td>{{ $record->date_of_order_actual ? \Carbon\Carbon::parse($record->date_of_order_actual)->format('Y-m-d') : '-' }}</td>
                                                <td>{{ $record->released_date_actual ? \Carbon\Carbon::parse($record->released_date_actual)->format('Y-m-d') : '-' }}</td>
                                                <td>
                                                    <a href="{{ route('orders-and-disposition.show', $record->id) }}" class="btn btn-info btn-sm" title="View">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('orders-and-disposition.edit', $record->id) }}" class="btn btn-warning btn-sm" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form action="{{ route('orders-and-disposition.destroy', $record->id) }}" method="POST" style="display:inline;">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger btn-sm delete-btn" title="Delete" onclick="return confirm('Are you sure you want to delete this order & disposition record?')">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="15" class="text-center">No orders & disposition records found.</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>


            <!-- Tab 6: Compliance & Awards -->
            <div class="tab-pane fade" id="tab6" role="tabpanel" aria-labelledby="tab6-tab">
                <div class="card shadow mb-4">
                    <div class="card-body">
                        <!-- Search + Buttons Row -->
                        <div class="d-flex justify-content-between align-items-center mb-3 custom-search-container">
                            <div class="d-flex align-items-center">
                                <label class="mr-2 mb-0" style="font-size: 0.8rem;">Search:</label>
                                <input type="search" class="form-control form-control-sm" id="customSearch6" placeholder="Search compliance records..." style="width: 200px;">
                            </div>
                        </div>

                        <!-- Table Container -->
                        <div class="table-container">
                            <table class="table table-bordered compact-table sticky-table" id="dataTable6" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>Inspection ID</th>
                                        <th>Name of Establishment</th>
                                        <th>Compliance Order Monetary Award</th>
                                        <th>OSH Penalty</th>
                                        <th>Affected Male</th>
                                        <th>Affected Female</th>
                                        <th>1st Order Dismissal - CNPC</th>
                                        <th>TAVable? (&lt; 10 workers)</th>
                                        <th>With Deposited Monetary Claims?</th>
                                        <th>Amount Deposited</th>
                                        <th>With Order Payment/Notice?</th>
                                        <th>Status (Employees Received)</th>
                                        <th>Status of Case (After 1st Order)</th>
                                        <th>Date Notice Finality Dismissed</th>
                                        <th>Released Date Notice Finality</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if(isset($complianceAndAwards) && $complianceAndAwards->count() > 0)
                                        @foreach($complianceAndAwards as $record)
                                            <tr>
                                                <td>{{ $record->inspection_id ?? '-' }}</td>
                                                <td title="{{ $record->establishment_name ?? '' }}">
                                                    {{ $record->establishment_name ? Str::limit($record->establishment_name, 25) : '-' }}
                                                </td>
                                                <td>{{ $record->compliance_order_monetary_award ?? '-' }}</td>
                                                <td>{{ $record->osh_penalty ?? '-' }}</td>
                                                <td>{{ $record->affected_male ?? 0 }}</td>
                                                <td>{{ $record->affected_female ?? 0 }}</td>
                                                <td>{{ $record->first_order_dismissal_cnpc ?? '-' }}</td>
                                                <td>{{ $record->tavable_less_than_10_workers ? 'Yes' : 'No' }}</td>
                                                <td>{{ $record->with_deposited_monetary_claims ? 'Yes' : 'No' }}</td>
                                                <td>{{ $record->amount_deposited ?? '-' }}</td>
                                                <td>{{ $record->with_order_payment_notice ? 'Yes' : 'No' }}</td>
                                                <td>
                                                    <span class="badge badge-{{ $record->status_all_employees_received == 'Yes' ? 'success' : 'warning' }}">
                                                        {{ $record->status_all_employees_received ?? 'Pending' }}
                                                    </span>
                                                </td>
                                                <td>{{ $record->status_case_after_first_order ?? '-' }}</td>
                                                <td>{{ $record->date_notice_finality_dismissed ? \Carbon\Carbon::parse($record->date_notice_finality_dismissed)->format('Y-m-d') : '-' }}</td>
                                                <td>{{ $record->released_date_notice_finality ? \Carbon\Carbon::parse($record->released_date_notice_finality)->format('Y-m-d') : '-' }}</td>
                                                <td>
                                                    <a href="{{ route('compliance-and-awards.show', $record->id) }}" class="btn btn-info btn-sm" title="View">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('compliance-and-awards.edit', $record->id) }}" class="btn btn-warning btn-sm" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form action="{{ route('compliance-and-awards.destroy', $record->id) }}" method="POST" style="display:inline;">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger btn-sm delete-btn" title="Delete" onclick="return confirm('Are you sure you want to delete this compliance & awards record?')">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="16" class="text-center">No compliance & awards records found.</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab 7: Appeals & Resolution -->
                        <div class="tab-pane fade" id="tab7" role="tabpanel" aria-labelledby="tab7-tab">
                <div class="card shadow mb-4">
                    <div class="card-body">
                        <!-- Search + Buttons Row -->
                        <div class="d-flex justify-content-between align-items-center mb-3 custom-search-container">
                            <div class="d-flex align-items-center">
                                <label class="mr-2 mb-0" style="font-size: 0.8rem;">Search:</label>
                                <input type="search" class="form-control form-control-sm" id="customSearch7" placeholder="Search appeals..." style="width: 200px;">
                            </div>
                        </div>

                        <!-- Table Container -->
                        <div class="table-container">
                            <table class="table table-bordered compact-table sticky-table" id="dataTable7" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>Inspection ID</th>
                                        <th>Name of Establishment</th>
                                        <th>Date Returned Case Mgmt</th>
                                        <th>Review CT CNPC</th>
                                        <th>Date Received Drafter Finalization (2nd)</th>
                                        <th>Date Returned Case Mgmt Signature (2nd)</th>
                                        <th>Date Order (2nd CNPC)</th>
                                        <th>Released Date (2nd CNPC)</th>
                                        <th>Date Forwarded MALSU</th>
                                        <th>Motion Reconsideration Date</th>
                                        <th>Date Received MALSU</th>
                                        <th>Date Resolution MR</th>
                                        <th>Released Date Resolution MR</th>
                                        <th>Date Appeal Received Records</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if(isset($appealsAndResolutions) && $appealsAndResolutions->count() > 0)
                                        @foreach($appealsAndResolutions as $record)
                                            <tr>
                                                <td>{{ $record->inspection_id ?? '-' }}</td>
                                                <td title="{{ $record->establishment_name ?? '' }}">
                                                    {{ $record->establishment_name ? Str::limit($record->establishment_name, 25) : '-' }}
                                                </td>
                                                <td>{{ $record->date_returned_case_mgmt ? \Carbon\Carbon::parse($record->date_returned_case_mgmt)->format('Y-m-d') : '-' }}</td>
                                                <td>{{ $record->review_ct_cnpc ?? '-' }}</td>
                                                <td>{{ $record->date_received_drafter_finalization_2nd ? \Carbon\Carbon::parse($record->date_received_drafter_finalization_2nd)->format('Y-m-d') : '-' }}</td>
                                                <td>{{ $record->date_returned_case_mgmt_signature_2nd ? \Carbon\Carbon::parse($record->date_returned_case_mgmt_signature_2nd)->format('Y-m-d') : '-' }}</td>
                                                <td>{{ $record->date_order_2nd_cnpc ? \Carbon\Carbon::parse($record->date_order_2nd_cnpc)->format('Y-m-d') : '-' }}</td>
                                                <td>{{ $record->released_date_2nd_cnpc ? \Carbon\Carbon::parse($record->released_date_2nd_cnpc)->format('Y-m-d') : '-' }}</td>
                                                <td>{{ $record->date_forwarded_malsu ? \Carbon\Carbon::parse($record->date_forwarded_malsu)->format('Y-m-d') : '-' }}</td>
                                                <td>{{ $record->motion_reconsideration_date ? \Carbon\Carbon::parse($record->motion_reconsideration_date)->format('Y-m-d') : '-' }}</td>
                                                <td>{{ $record->date_received_malsu ? \Carbon\Carbon::parse($record->date_received_malsu)->format('Y-m-d') : '-' }}</td>
                                                <td>{{ $record->date_resolution_mr ? \Carbon\Carbon::parse($record->date_resolution_mr)->format('Y-m-d') : '-' }}</td>
                                                <td>{{ $record->released_date_resolution_mr ? \Carbon\Carbon::parse($record->released_date_resolution_mr)->format('Y-m-d') : '-' }}</td>
                                                <td>{{ $record->date_appeal_received_records ? \Carbon\Carbon::parse($record->date_appeal_received_records)->format('Y-m-d') : '-' }}</td>
                                                <td>
                                                    <a href="{{ route('appeals-and-resolution.show', $record->id) }}" class="btn btn-info btn-sm" title="View">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('appeals-and-resolution.edit', $record->id) }}" class="btn btn-warning btn-sm" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form action="{{ route('appeals-and-resolution.destroy', $record->id) }}" method="POST" style="display:inline;">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger btn-sm delete-btn" title="Delete" onclick="return confirm('Are you sure you want to delete this appeals & resolution record?')">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="15" class="text-center">No appeals & resolution records found.</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
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
                                <select class="form-control" id="overall_status" name="overall_status" required>
                                    <option value="Active">Active</option>
                                    <option value="Completed">Completed</option>
                                    <option value="Dismissed">Dismissed</option>
                                </select>
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
@endsection

@section('scripts')
   
<script>
$(document).ready(function() {
    // Check if DataTable is available
    if (typeof $.fn.DataTable === 'undefined') {
        console.error('DataTables library is not loaded. Please include DataTables CSS and JS files.');
        return;
    }

    // Destroy existing DataTables if they exist
    if ($.fn.DataTable.isDataTable('#dataTable0')) {
        $('#dataTable0').DataTable().destroy();
    }
    if ($.fn.DataTable.isDataTable('#dataTable1')) {
        $('#dataTable1').DataTable().destroy();
    }
    if ($.fn.DataTable.isDataTable('#dataTable2')) {
        $('#dataTable2').DataTable().destroy();
    }

    // Initialize DataTable for All Active Cases Tab (tab0)
    var table0 = $('#dataTable0').DataTable({
        pageLength: 10,
        lengthChange: false,
        paging: true,
        searching: false,
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
    });

    // Initialize DataTable for Inspection Tab (tab1)
    var table1 = $('#dataTable1').DataTable({
        pageLength: 10,
        lengthChange: false,
        paging: true,
        searching: false,
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
    });

    // Initialize DataTable for Docketing Tab (tab2)
    var table2 = $('#dataTable2').DataTable({
        pageLength: 10,
        lengthChange: false,
        paging: true,
        searching: false,
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
    });

    // Custom search functionality for All Active Cases Tab
    $('#customSearch0').on('keyup input change', function() {
        table0.search(this.value).draw();
    });

    // Custom search functionality for Inspection Tab
    $('#customSearch1').on('keyup input change', function() {
        table1.search(this.value).draw();
    });

    // Custom search functionality for Docketing Tab
    $('#customSearch2').on('keyup input change', function() {
        table2.search(this.value).draw();
    });

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

    // FIXED: Single delete handler for ALL delete buttons
    $(document).on('click', '.delete-btn', function(e) {
        e.preventDefault();
        console.log('Delete button clicked');
        
        const button = $(this);
        const caseId = button.data('case-id');
        const row = button.closest('tr');
        
        console.log('Case ID:', caseId);
        
        // Check if CSRF token exists
        const csrfToken = $('meta[name="csrf-token"]').attr('content');
        console.log('CSRF Token:', csrfToken);
        
        if (!csrfToken) {
            showAlert('error', 'CSRF token not found. Please refresh the page.');
            return;
        }
        
        // Show confirmation dialog
        if (confirm('Are you sure you want to delete this case? This action cannot be undone.')) {
            console.log('User confirmed deletion');
            
            // Show loading state
            button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
            
            // FIXED: Use correct URL format to match your routes
            // Your route is: Route::delete('/case/{id}', ...)
            $.ajax({
                url: `/case/${caseId}`, // Matches your route: /case/{id}
                type: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                beforeSend: function() {
                    console.log('AJAX request starting...');
                },
                success: function(response) {
                    console.log('Success response:', response);
                    
                    // Remove the row from table
                    row.fadeOut(300, function() {
                        $(this).remove();
                        
                        // Check if table is empty and show message
                        const tableBody = $('#dataTable0 tbody, #dataTable1 tbody').filter(':visible');
                        if (tableBody.find('tr:visible').length === 0) {
                            const colspan = tableBody.closest('table').find('thead th').length;
                            tableBody.html(
                                `<tr><td colspan="${colspan}" class="text-center">No records found.</td></tr>`
                            );
                        }
                    });
                    
                    // Show success message
                    showAlert('success', response.message || 'Record deleted successfully!');
                },
                error: function(xhr, status, error) {
                    console.log('=== ERROR RESPONSE ===');
                    console.log('Error occurred:', xhr, status, error);
                    console.log('Status Code:', xhr.status);
                    console.log('Response Text:', xhr.responseText);
                    console.log('Response Headers:', xhr.getAllResponseHeaders());
                    
                    // Re-enable button
                    button.prop('disabled', false).html('<i class="fas fa-trash"></i>');
                    
                    // Show error message
                    let errorMessage = 'Failed to delete record.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    } else if (xhr.status === 404) {
                        errorMessage = 'Record not found.';
                    } else if (xhr.status === 500) {
                        errorMessage = 'Server error occurred.';
                    }
                    showAlert('error', errorMessage);
                }
            });
        } else {
            console.log('User cancelled deletion');
        }
    });

    // Handle edit button click for Cases
    $(document).on('click', '.btn-warning[data-target="#addCaseModal"]', function() {
        var caseId = $(this).data('case-id');
        if (caseId) {
            $.get('/case/' + caseId + '/edit', function(data) {
                // Populate form fields with existing data
                $('#inspection_id').val(data.inspection_id);
                $('#case_no').val(data.case_no);
                $('#establishment_name').val(data.establishment_name);
                $('#current_stage').val(data.current_stage);
                $('#overall_status').val(data.overall_status);
                
                // Change form action to update
                $('#caseForm').attr('action', '/case/' + caseId);
                $('#formMethod').val('PUT');
            });
        }
    });

    // Auto-dismiss alerts after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);

    // Handle tab switching - reinitialize DataTables when switching tabs
    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        var target = $(e.target).attr("href");
        if (target === '#tab0') {
            table0.columns.adjust().draw();
        } else if (target === '#tab1') {
            table1.columns.adjust().draw();
        } else if (target === '#tab2') {
            table2.columns.adjust().draw();
        }
    });
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
    
    // Add alert to the active tab's card-body
    $('.tab-pane.active .card-body').prepend(alertHtml);
    
    // Auto-remove after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut();
    }, 5000);
}

            $(document).ready(function() {
                // Unified inline editing system
                let currentEditingRow = null;
                let originalData = {};
                let currentTab = null;

                // Tab configuration - easily add more tabs here
                const tabConfigs = {
                    'tab0': {
                        name: 'case',
                        endpoint: '/case/',
                        editBtnClass: '.edit-row-btn-case',
                        saveBtnClass: '.save-btn-case', 
                        cancelBtnClass: '.cancel-btn-case',
                        alertPrefix: 'tab0',
                        fields: {
                            'inspection_id': { type: 'text' },
                            'case_no': { type: 'text' },
                            'establishment_name': { type: 'text' },
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
                            }
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
                            // Remove 'lapse_20_day_period' from here - it's no longer editable
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
                            // inspection_id and establishment_name are readonly (from case)
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
                    }
                    // Add more tabs here as needed:
                    // 'tab2': { name: 'docketing', endpoint: '/docketing/', ... }
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
                $(document).on('click', '.edit-row-btn, .edit-row-btn-case, .edit-row-btn-docketing', function() {
                    const row = $(this).closest('tr');
                    currentTab = getCurrentTab();
                    
                    // Cancel any existing edit
                    if (currentEditingRow && currentEditingRow.get(0) !== row.get(0)) {
                        cancelEdit();
                    }
                    
                    enableRowEdit(row);
                });

                // Unified save button click handler  
                $(document).on('click', '.save-btn, .save-btn-case', function() {
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
                $(document).on('click', '.cancel-btn, .cancel-btn-case', function() {
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
                        $(`.save-btn, .save-btn-case`).filter(':visible').click();
                    }
                });

                function enableRowEdit(row) {
                    currentEditingRow = row;
                    const config = getTabConfig(currentTab);
                    originalData = {};
                    
                    row.find('.editable-cell:not(.readonly-cell)').each(function() {  // Added :not(.readonly-cell)
                        const cell = $(this);
                        const field = cell.data('field');
                        originalData[field] = cell.text().trim();
                        
                        const input = createInput(field, cell, config);
                        cell.html(input);
                        cell.addClass('edit-mode');
                    });
                    
                    // Replace action buttons
                    const actionsCell = row.find('td:last');
                    const currentButtons = actionsCell.html();
                    actionsCell.data('original-buttons', currentButtons);
                    
                    const buttonClass = config.name === 'case' ? 'case' : '';
                    actionsCell.html(`
                        <div class="save-cancel-buttons">
                            <button class="btn btn-success btn-sm save-btn${buttonClass ? '-' + buttonClass : ''}" title="Save">
                                <i class="fas fa-check"></i>
                            </button>
                            <button class="btn btn-secondary btn-sm cancel-btn${buttonClass ? '-' + buttonClass : ''} ml-1" title="Cancel">
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
                    } else {
                        // Handle establishment name with full title
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

                    // Clean data
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
                        let displayValue = responseData[field];
                        
                        if (displayValue === null || displayValue === undefined || displayValue === '') {
                            displayValue = '-';
                        }
                        
                        // Handle special display formats
                        if (field === 'current_stage' && displayValue.includes(': ')) {
                            displayValue = displayValue.split(': ')[1];
                        }
                        
                        // NEW: Handle status_docket badge display
                            if (field === 'status_docket' && displayValue !== '-') {
                                const badgeClass = displayValue === 'Pending' ? 'warning' : 
                                                (displayValue === 'Completed' ? 'success' : 'secondary');
                                displayValue = `<span class="badge badge-${badgeClass}">${displayValue}</span>`;
                            }

                        if (field === 'establishment_name' && displayValue !== '-') {
                            cell.attr('title', displayValue);
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
                    
                    currentEditingRow.find('.editable-cell:not(.readonly-cell)').each(function() {  // Added :not(.readonly-cell)
                        const cell = $(this);
                        const field = cell.data('field');
                        let displayValue = originalData[field] || '';
                        
                        // Handle display formats
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
                    
                    // Fallback to generic IDs if specific ones don't exist
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
                    
                    // Handle status codes
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

                // Unified hideAlert function
                window.hideAlert = function(alertId) {
                    $(`#${alertId}`).removeClass('show').addClass('fade');
                    setTimeout(() => $(`#${alertId}`).hide(), 150);
                };

                // Handle tab switching - cancel any active edits
                $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
                    if (currentEditingRow) {
                        cancelEdit();
                    }
                });
            });

            // Utility functions
            function showAlert(message, type) {
                const alertId = type === 'success' ? 'success-alert' : 'error-alert';
                const messageId = type === 'success' ? 'success-message' : 'error-message';
                
                $(`#${messageId}`).text(message);
                $(`#${alertId}`).removeClass('fade').addClass('show').show();
                
                // Auto-hide after 5 seconds
                setTimeout(() => {
                    hideAlert(alertId);
                }, 3000);
            }

            function hideAlert(alertId) {
                $(`#${alertId}`).removeClass('show').addClass('fade');
                setTimeout(() => {
                    $(`#${alertId}`).hide();
                }, 150);
            }

        function showAddForm() {
            alert('Add Inspection form would open here');
            // Implement your add inspection logic
        }

        function confirmDelete(inspectionId) {
            if (confirm('Are you sure you want to delete this inspection?')) {
                // Implement delete logic
                alert(`Delete inspection ID: ${inspectionId}`);
                // You would typically make an AJAX call here
            }
        }

        function confirmNextStage(inspectionId) {
            if (confirm('Complete inspection and move to Docketing?')) {
                // Implement next stage logic
                alert(`Move inspection ID: ${inspectionId} to next stage`);
                // You would typically make an AJAX call here
            }
        }
</script>
@endsection
