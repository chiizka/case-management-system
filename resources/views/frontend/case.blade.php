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

/* Sticky table styling */
.sticky-table {
    position: relative;
    width: 100%;
    border-collapse: collapse; /* Ensure no gaps */
}

/* Sticky columns (1–5) */
.sticky-table th:nth-child(1),
.sticky-table td:nth-child(1) {
    position: sticky;
    left: 0;
    background-color: #fff;
    z-index: 10;
    border-right: 2px solid #dee2e6;
    min-width: 80px;
}

.sticky-table th:nth-child(2),
.sticky-table td:nth-child(2) {
    position: sticky;
    left: 80px;
    background-color: #fff;
    z-index: 10;
    border-right: 2px solid #dee2e6;
    min-width: 90px;
}

.sticky-table th:nth-child(3),
.sticky-table td:nth-child(3) {
    position: sticky;
    left: 170px;
    background-color: #fff;
    z-index: 10;
    border-right: 2px solid #dee2e6;
    min-width: 120px;
}

.sticky-table th:nth-child(4),
.sticky-table td:nth-child(4) {
    position: sticky;
    left: 290px;
    background-color: #fff;
    z-index: 10;
    border-right: 2px solid #dee2e6;
    min-width: 150px;
}

.sticky-table th:nth-child(5),
.sticky-table td:nth-child(5) {
    position: sticky;
    left: 440px;
    background-color: #fff;
    z-index: 10;
    border-right: 2px solid #dee2e6;
    min-width: 150px;
}

/* Sticky header */
.sticky-table thead th {
    position: sticky;
    top: 0;
    background-color: #f8f9fa !important;
    border-bottom: 2px solid #dee2e6;
    z-index: 12; /* Higher z-index to ensure headers stay above sticky columns */
    box-shadow: inset 0 -1px 0 #dee2e6; /* Subtle shadow to prevent gaps */
}

/* Ensure sticky header cells for columns 1–5 stay above other headers */
.sticky-table thead th:nth-child(-n+5) {
    z-index: 13; /* Higher than other headers and content */
}

/* Ensure non-sticky columns have proper background */
.sticky-table td:nth-child(n+6),
.sticky-table th:nth-child(n+6) {
    background-color: #fff;
    min-width: 100px;
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
                <a class="nav-link active" id="tab1-tab" data-toggle="tab" href="#tab1" role="tab" aria-controls="tab1" aria-selected="true">
                    Active Cases
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="tab2-tab" data-toggle="tab" href="#tab2" role="tab" aria-controls="tab2" aria-selected="false">
                    Provincial Cases
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="tab3-tab" data-toggle="tab" href="#tab3" role="tab" aria-controls="tab3" aria-selected="false">
                    Regional Cases
                </a>
            </li>
        </ul>

        <!-- Tabs Content -->
        <div class="tab-content mt-3" id="dataTableTabsContent">
            <div class="tab-pane fade show active" id="tab1" role="tabpanel" aria-labelledby="tab1-tab">
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

                        <!-- Search + Buttons Row -->
                        <div class="d-flex justify-content-between align-items-center mb-3 custom-search-container">
                            <div class="d-flex align-items-center">
                                <label class="mr-2 mb-0" style="font-size: 0.8rem;">Search:</label>
                                <input type="search" class="form-control form-control-sm" id="customSearch1" placeholder="Search cases..." style="width: 200px;">
                            </div>
                            <div>
                                <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addCaseModal" data-mode="add">
                                    + Add Case
                                </button>
                            </div>
                        </div>

                        <!-- Table Container -->
                        <div class="table-container">
                            <table class="table table-bordered compact-table sticky-table" id="dataTable1" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>Case No.</th>
                                        <th>Case Status</th>
                                        <th>Type of Case</th>
                                        <th>Complainant Info</th>
                                        <th>Respondent Info</th>
                                        <th>Case Details</th>
                                        <th>Date Filed</th>
                                        <th>Assigned To</th>
                                        <th>Priority Level</th>
                                        <th>Court Location</th>
                                        <th>Judge Assigned</th>
                                        <th>Case Category</th>
                                        <th>Filing Fee</th>
                                        <th>Last Updated</th>
                                        <th>Next Hearing</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if(isset($cases) && $cases->count() > 0)
                                        @foreach($cases as $case)
                                            <tr>
                                                <td>{{ $case->case_number }}</td>
                                                <td>
                                                    <span class="badge 
                                                        @if($case->case_status == 'Active') badge-success
                                                        @elseif($case->case_status == 'Pending') badge-warning
                                                        @elseif($case->case_status == 'Closed') badge-secondary
                                                        @else badge-info
                                                        @endif
                                                    ">{{ $case->case_status }}</span>
                                                </td>
                                                <td>{{ $case->case_type }}</td>
                                                <td title="{{ $case->complainant }}">{{ Str::limit($case->complainant, 20) }}</td>
                                                <td title="{{ $case->respondent }}">{{ Str::limit($case->respondent, 20) }}</td>
                                                <td title="{{ $case->case_details }}">{{ Str::limit($case->case_details, 30) }}</td>
                                                <td>{{ \Carbon\Carbon::parse($case->date_filed)->format('Y-m-d') }}</td>
                                                <td>John Doe</td>
                                                <td><span class="badge badge-danger">High</span></td>
                                                <td>Main Court</td>
                                                <td>Judge Smith</td>
                                                <td>Civil</td>
                                                <td>$500.00</td>
                                                <td>{{ now()->format('Y-m-d') }}</td>
                                                <td>{{ now()->addDays(7)->format('Y-m-d') }}</td>
                                                <td>
                                                    <button class="btn btn-warning" data-toggle="modal" data-target="#addCaseModal" data-mode="edit" data-case-id="{{ $case->id }}" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="btn btn-danger delete-btn" data-case-id="{{ $case->id }}" title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                    <button class="btn btn-info" title="View">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="16" class="text-center">No cases found. Click "Add Case" to create your first case.</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab 2 -->
            <div class="tab-pane fade" id="tab2" role="tabpanel" aria-labelledby="tab2-tab">
                <div class="card shadow mb-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3 custom-search-container">
                            <div class="d-flex align-items-center">
                                <label class="mr-2 mb-0" style="font-size: 0.8rem;">Search:</label>
                                <input type="search" class="form-control form-control-sm" id="customSearch2" placeholder="Search cases..." style="width: 200px;">
                            </div>
                            <div>
                                <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addCaseModal" data-mode="add">
                                    + Add Case
                                </button>
                            </div>
                        </div>
                        <div class="table-container">
                            <table class="table table-bordered compact-table sticky-table" id="dataTable2" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>Case No.</th>
                                        <th>Case Status</th>
                                        <th>Type of Case</th>
                                        <th>Complainant Info</th>
                                        <th>Respondent Info</th>
                                        <th>Case Details</th>
                                        <th>Date Filed</th>
                                        <th>Assigned To</th>
                                        <th>Priority Level</th>
                                        <th>Court Location</th>
                                        <th>Judge Assigned</th>
                                        <th>Case Category</th>
                                        <th>Filing Fee</th>
                                        <th>Last Updated</th>
                                        <th>Next Hearing</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>PROV-001</td>
                                        <td><span class="badge badge-success">Active</span></td>
                                        <td>Administrative Case</td>
                                        <td title="Provincial Office - Main Branch">Provincial Office</td>
                                        <td title="Local Municipality of District 1">Local Municipality</td>
                                        <td title="Budget allocation dispute regarding infrastructure projects">Budget allocation dispute</td>
                                        <td>2024-02-20</td>
                                        <td>Jane Smith</td>
                                        <td><span class="badge badge-warning">Medium</span></td>
                                        <td>Provincial Court</td>
                                        <td>Judge Johnson</td>
                                        <td>Administrative</td>
                                        <td>$750.00</td>
                                        <td>2024-03-01</td>
                                        <td>2024-03-15</td>
                                        <td>
                                            <button class="btn btn-warning" data-toggle="modal" data-target="#addCaseModal" data-mode="edit">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                            <button class="btn btn-info">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>PROV-002</td>
                                        <td><span class="badge badge-warning">Pending</span></td>
                                        <td>Environmental Case</td>
                                        <td title="Environmental Protection Group">Environmental Group</td>
                                        <td title="Mountain Mining Corporation Ltd.">Mining Company</td>
                                        <td title="Environmental compliance and permit violations">Environmental compliance</td>
                                        <td>2024-03-01</td>
                                        <td>Bob Wilson</td>
                                        <td><span class="badge badge-danger">High</span></td>
                                        <td>Environmental Court</td>
                                        <td>Judge Davis</td>
                                        <td>Environmental</td>
                                        <td>$1,200.00</td>
                                        <td>2024-03-05</td>
                                        <td>2024-03-20</td>
                                        <td>
                                            <button class="btn btn-warning" data-toggle="modal" data-target="#addCaseModal" data-mode="edit">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                            <button class="btn btn-info">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab 3 -->
            <div class="tab-pane fade" id="tab3" role="tabpanel" aria-labelledby="tab3-tab">
                <div class="card shadow mb-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3 custom-search-container">
                            <div class="d-flex align-items-center">
                                <label class="mr-2 mb-0" style="font-size: 0.8rem;">Search:</label>
                                <input type="search" class="form-control form-control-sm" id="customSearch3" placeholder="Search cases..." style="width: 200px;">
                            </div>
                            <div>
                                <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addCaseModal" data-mode="add">
                                    + Add Case
                                </button>
                            </div>
                        </div>
                        <div class="table-container">
                            <table class="table table-bordered compact-table sticky-table" id="dataTable3" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>Case No.</th>
                                        <th>Case Status</th>
                                        <th>Type of Case</th>
                                        <th>Complainant Info</th>
                                        <th>Respondent Info</th>
                                        <th>Case Details</th>
                                        <th>Date Filed</th>
                                        <th>Assigned To</th>
                                        <th>Priority Level</th>
                                        <th>Court Location</th>
                                        <th>Judge Assigned</th>
                                        <th>Case Category</th>
                                        <th>Filing Fee</th>
                                        <th>Last Updated</th>
                                        <th>Next Hearing</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>REG-001</td>
                                        <td><span class="badge badge-success">Active</span></td>
                                        <td>Inter-provincial Case</td>
                                        <td title="Regional Development Authority">Regional Authority</td>
                                        <td title="Multi-Province Trading Corporation">Multi-Province Corp</td>
                                        <td title="Cross-border trade dispute regarding tariffs">Cross-border trade dispute</td>
                                        <td>2024-01-30</td>
                                        <td>Alice Brown</td>
                                        <td><span class="badge badge-danger">High</span></td>
                                        <td>Regional Court</td>
                                        <td>Judge Wilson</td>
                                        <td>Commercial</td>
                                        <td>$2,000.00</td>
                                        <td>2024-02-15</td>
                                        <td>2024-03-10</td>
                                        <td>
                                            <button class="btn btn-warning" data-toggle="modal" data-target="#addCaseModal" data-mode="edit">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                            <button class="btn btn-info">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>REG-002</td>
                                        <td><span class="badge badge-secondary">Closed</span></td>
                                        <td>Regional Policy Case</td>
                                        <td title="Citizens Alliance for Justice">Citizens Alliance</td>
                                        <td title="Regional Government Administration">Regional Government</td>
                                        <td title="Policy implementation challenge regarding new regulations">Policy implementation challenge</td>
                                        <td>2024-02-15</td>
                                        <td>Mike Johnson</td>
                                        <td><span class="badge badge-info">Low</span></td>
                                        <td>Administrative Court</td>
                                        <td>Judge Martinez</td>
                                        <td>Policy</td>
                                        <td>$300.00</td>
                                        <td>2024-02-28</td>
                                        <td>Completed</td>
                                        <td>
                                            <button class="btn btn-warning" data-toggle="modal" data-target="#addCaseModal" data-mode="edit">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                            <button class="btn btn-info">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div> <!-- End Tabs Content -->
    </div>
    <!-- /.container-fluid -->
</div>
<!-- End of Main Content -->

<!-- Modal -->
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
                <form id="caseForm" method="post" action="{{ route('case.store') }}">
                    @csrf
                    @method('post')
                    <input type="hidden" id="rowIndex" value="">
                    <input type="hidden" id="tableId" value="">
                    <div class="form-group">
                        <label for="caseNumber">Case No.</label>
                        <input type="text" class="form-control" id="caseNumber" name="case_number" placeholder="Enter case number">
                    </div>
                    <div class="form-group">
                        <label for="caseStatus">Case Status</label>
                        <select class="form-control" id="caseStatus" name="case_status">
                            <option value="">Select status</option>
                            <option value="Active">Active</option>
                            <option value="Pending">Pending</option>
                            <option value="Closed">Closed</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="caseType">Type of Case</label>
                        <input type="text" class="form-control" id="caseType" name="case_type" placeholder="Enter type of case">
                    </div>
                    <div class="form-group">
                        <label for="complainantInfo">Complainant Information</label>
                        <textarea class="form-control" id="complainantInfo" name="complainant" rows="3" placeholder="Enter complainant information"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="respondentInfo">Respondent Information</label>
                        <textarea class="form-control" id="respondentInfo" name="respondent" rows="3" placeholder="Enter respondent information"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="caseDetails">Case Details</label>
                        <textarea class="form-control" id="caseDetails" name="case_details" rows="3" placeholder="Enter case details"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="dateFiled">Date Filed</label>
                        <input type="date" class="form-control" name="date_filed" id="dateFiled">
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
    ['#dataTable1', '#dataTable2', '#dataTable3'].forEach(function(tableId) {
        if ($.fn.DataTable.isDataTable(tableId)) {
            $(tableId).DataTable().destroy();
        }
    });

    // Initialize DataTables with proper configuration
    var tables = {};
    
    tables.table1 = $('#dataTable1').DataTable({
        pageLength: 5,
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
            // Re-apply sticky positioning after DataTables redraw
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
    
    tables.table2 = $('#dataTable2').DataTable({
        pageLength: 5,
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
    
    tables.table3 = $('#dataTable3').DataTable({
        pageLength: 5,
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

    // Custom search functionality
    $('#customSearch1').on('keyup input change', function() {
        tables.table1.search(this.value).draw();
    });
    
    $('#customSearch2').on('keyup input change', function() {
        tables.table2.search(this.value).draw();
    });
    
    $('#customSearch3').on('keyup input change', function() {
        tables.table3.search(this.value).draw();
    });

    // Clear search when switching tabs
    $('a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
        $('#customSearch1, #customSearch2, #customSearch3').val('');
        Object.values(tables).forEach(function(table) {
            table.search('').draw();
        });
    });

    // Modal handling for Add/Edit
    $('#addCaseModal').on('show.bs.modal', function(event) {
        var button = $(event.relatedTarget);
        var mode = button.data('mode') || 'add';
        var modal = $(this);
        
        modal.find('#addCaseModalLabel').text(mode === 'add' ? 'Add New Case' : 'Edit Case');

        if (mode === 'edit') {
            var caseId = button.data('case-id');
            // Implement AJAX to fetch case details if needed
        } else {
            modal.find('#caseForm')[0].reset();
        }
    });

    // Auto-dismiss alerts after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);

    console.log('DataTables initialized successfully with sticky columns and headers');
});
</script>
@stop