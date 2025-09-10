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
                                            <tr>
                                                <td>{{ $case->inspection_id }}</td>
                                                <td>{{ $case->case_no ?? '-' }}</td>
                                                <td title="{{ $case->establishment_name }}">{{ Str::limit($case->establishment_name, 25) }}</td>
                                                <td>{{ $case->current_stage }}</td>
                                                <td>{{ $case->overall_status }}</td>
                                                <td>{{ $case->created_at ? \Carbon\Carbon::parse($case->created_at)->format('Y-m-d') : '-' }}</td>
                                                <td>
                                                    <button class="btn btn-warning" data-toggle="modal" data-target="#addCaseModal" data-mode="edit" data-case-id="{{ $case->id }}" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <form action="/case/{{ $case->id }}" method="POST" style="display:inline;">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Delete this case?')" title="Delete">
                                                            <i class="fas fa-trash"></i>  
                                                        </button>
                                                    </form>
                                                    <button class="btn btn-info" title="View">
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
                                        <th>Case</th>
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
                                            <tr>
                                                <td>{{ $inspection->case->establishment_name ?? '-' }}</td>
                                                <td>{{ $inspection->case->inspection_id ?? '-' }}</td>
                                                <td title="{{ $inspection->case->establishment_name ?? '' }}">
                                                {{ $inspection->case ? Str::limit($inspection->case->establishment_name, 25) : '-' }}</td>
                                                <td>{{ $inspection->po_office ?? '-' }}</td>
                                                <td>{{ $inspection->inspector_name ?? '-' }}</td>
                                                <td>{{ $inspection->inspector_authority_no ?? '-' }}</td>
                                                <td>{{ $inspection->date_of_inspection ? \Carbon\Carbon::parse($inspection->date_of_inspection)->format('Y-m-d') : '-' }}</td>
                                                <td>{{ $inspection->date_of_nr ? \Carbon\Carbon::parse($inspection->date_of_nr)->format('Y-m-d') : '-' }}</td>
                                                <td>{{ $inspection->lapse_20_day_period ? \Carbon\Carbon::parse($inspection->lapse_20_day_period)->format('Y-m-d') : '-' }}</td>
                                                <td>{{ $inspection->twg_ali ?? '-' }}</td>
                                                <td>
                                                    <a href="{{ route('inspection.show', $inspection->id) }}" class="btn btn-info btn-sm" title="View">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('inspection.edit', $inspection->id) }}" class="btn btn-warning btn-sm" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form action="{{ route('inspection.destroy', $inspection->id) }}" method="POST" style="display:inline;">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger btn-sm delete-btn" title="Delete" onclick="return confirm('Are you sure you want to delete this inspection?')">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="11" class="text-center">No inspections found. Click "Add Inspection" to create your first inspection.</td>
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
                                            <tr>
                                                <td>{{ $record->case->inspection_id ?? '-' }}</td>
                                                <td title="{{ $record->case->establishment_name ?? '' }}">
                                                {{ $record->case ? Str::limit($record->case->establishment_name, 25) : '-' }}</td>
                                                <td>{{ $record->pct_for_docketing ?? '-' }}</td>
                                                <td>{{ $record->date_scheduled_docketed ? \Carbon\Carbon::parse($record->date_scheduled_docketed)->format('Y-m-d') : '-' }}</td>
                                                <td>{{ $record->aging_docket ?? '-' }}</td>
                                                <td>
                                                    <span class="badge badge-{{ $record->status_docket == 'Pending' ? 'warning' : ($record->status_docket == 'Completed' ? 'success' : 'secondary') }}">
                                                        {{ $record->status_docket ?? 'Pending' }}
                                                    </span>
                                                </td>
                                                <td>{{ $record->hearing_officer_mis ?? '-' }}</td>
                                                <td>
                                                    <a href="{{ route('docketing.show', $record->id) }}" class="btn btn-info btn-sm" title="View">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('docketing.edit', $record->id) }}" class="btn btn-warning btn-sm" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form action="{{ route('docketing.destroy', $record->id) }}" method="POST" style="display:inline;">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger btn-sm delete-btn" title="Delete" onclick="return confirm('Are you sure you want to delete this docketing record?')">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
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
                                        <th>1st MC PCT (15 days after lapse)</th>
                                        <th>Status (1st MC)</th>
                                        <th>Date of 2nd/Last MC (Actual)</th>
                                        <th>2nd/Last MC PCT (30 days from 1st MC)</th>
                                        <th>Status (2nd MC)</th>
                                        <th>Case Folder forwarded to RO (Actual)</th>
                                        <th>Complete case folder? (Y/N)</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td colspan="11" class="text-center">No hearing process records found.</td>
                                    </tr>
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
                                        <th>Final Review (Date received)</th>
                                        <th>Date Received by Drafter for Finalization</th>
                                        <th>Date Returned to Case Mngt for Signature</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td colspan="18" class="text-center">No review & drafting records found.</td>
                                    </tr>
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
                                        <th>Aging (2 days for finalization)</th>
                                        <th>Status (Finalization)</th>
                                        <th>PCT (96 days from date of NR)</th>
                                        <th>Date Signed (MIS)</th>
                                        <th>Status (PCT)</th>
                                        <th>Reference Date (PCT)</th>
                                        <th>Aging (PCT) - Must be less than 75 days</th>
                                        <th>Disposition (MIS)</th>
                                        <th>Disposition (Actual)</th>
                                        <th>Findings to be complied in the Order</th>
                                        <th>Date of Order (Actual)</th>
                                        <th>Released Date (Actual stamped by Records)</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td colspan="15" class="text-center">No orders & disposition records found.</td>
                                    </tr>
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
                                        <th>Compliance Order Monetary Award (Actual)</th>
                                        <th>OSH Penalty</th>
                                        <th>Affected Male</th>
                                        <th>Affected Female</th>
                                        <th>1st Order Dismissal - CNPC</th>
                                        <th>TAVable? (Less than 10 workers)</th>
                                        <th>Scanned Order (1st Order)</th>
                                        <th>With DEPOSITED Monetary Claims in DOLE 5?</th>
                                        <th>Amount Deposited (DOLE 5 Cashier)</th>
                                        <th>With Order of Payment/Notice to Claim Award?</th>
                                        <th>Status (if all affected employees received claim)</th>
                                        <th>Status of Case after 1st Order</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td colspan="15" class="text-center">No compliance & awards records found.</td>
                                    </tr>
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
                                        <th>Date Returned to Case Mngt (C&T/CNPC)</th>
                                        <th>Review (C&T/CNPC)</th>
                                        <th>Date Received by Drafter for Finalization (2nd Order)</th>
                                        <th>Date Returned to Case Mngt for Signature (2nd Order)</th>
                                        <th>Date of Order (2nd Order/CNPC)</th>
                                        <th>Released Date (2nd Order/CNPC)</th>
                                        <th>Scanned Order (2nd Order/CNPC)</th>
                                        <th>Date forwarded to MALSU</th>
                                        <th>Scanned Copy of Indorsement to MALSU</th>
                                        <th>Motion for Reconsideration (Date received)</th>
                                        <th>Date Received by MALSU</th>
                                        <th>Date of Resolution (MR)</th>
                                        <th>Released Date of Resolution (MR)</th>
                                        <th>Scanned Resolution (MR)</th>
                                        <th>Date of Appeal (Date received by Records unit)</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td colspan="18" class="text-center">No appeals & resolution records found.</td>
                                    </tr>
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
                                <input type="number" class="form-control" id="current_stage" name="current_stage" min="1" max="7" placeholder="Enter stage (1-7)" required>
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
</script>
@stop