@extends('frontend.layouts.app')
@section('content')

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
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <!-- Search on the left -->
                            <div class="d-flex align-items-center">
                                <label class="mr-2 mb-0">Search:</label>
                                <input type="search" class="form-control form-control-sm" id="customSearch1" placeholder="Search cases..." style="width: 200px;">
                            </div>

                            <!-- Buttons on the right -->
                            <div>
                                <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addCaseModal" data-mode="add">
                                    + Add Case
                                </button>
                            </div>
                        </div>

                        <!-- Table -->
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm custom-table" id="dataTable1" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>Case No.</th>
                                        <th>Case Status</th>
                                        <th>Type of Case</th>
                                        <th>Complainant Information</th>
                                        <th>Respondent Information</th>
                                        <th>Case Details</th>
                                        <th>Date filed</th>
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
                                                <td>{{ $case->complainant }}</td>
                                                <td>{{ $case->respondent }}</td>
                                                <td>{{ Str::limit($case->case_details, 50) }}</td>
                                                <td>{{ \Carbon\Carbon::parse($case->date_filed)->format('Y-m-d') }}</td>
                                                <td>
                                                    <button class="btn btn-sm btn-warning" data-toggle="modal" data-target="#addCaseModal" data-mode="edit" data-case-id="{{ $case->id }}" title="Edit Case">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-danger delete-btn" data-case-id="{{ $case->id }}" title="Delete Case">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="8" class="text-center">No cases found. Click "Add Case" to create your first case.</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                        <!-- DataTable Pagination and Info will appear here -->
                    </div>
                </div>
            </div>

            <!-- Tab 2 -->
            <div class="tab-pane fade" id="tab2" role="tabpanel" aria-labelledby="tab2-tab">
                <div class="card shadow mb-4">
                    <div class="card-body">
                        <!-- Search + Buttons Row for Tab 2 -->
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <!-- Search on the left -->
                            <div class="d-flex align-items-center">
                                <label class="mr-2 mb-0">Search:</label>
                                <input type="search" class="form-control form-control-sm" id="customSearch2" placeholder="Search cases..." style="width: 200px;">
                            </div>

                            <!-- Buttons on the right -->
                            <div>
                                <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addCaseModal" data-mode="add">
                                    + Add Case
                                </button>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered table-sm custom-table" id="dataTable2" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>Case No.</th>
                                        <th>Case Status</th>
                                        <th>Type of Case</th>
                                        <th>Complainant Information</th>
                                        <th>Respondent Information</th>
                                        <th>Case Details</th>
                                        <th>Date filed</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>PROV-001</td>
                                        <td>Active</td>
                                        <td>Administrative Case</td>
                                        <td>Provincial Office</td>
                                        <td>Local Municipality</td>
                                        <td>Budget allocation dispute</td>
                                        <td>2024-02-20</td>
                                        <td>
                                            <button class="btn btn-sm btn-warning" data-toggle="modal" data-target="#addCaseModal" data-mode="edit">Edit</button>
                                            <button class="btn btn-sm btn-danger">Delete</button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>PROV-002</td>
                                        <td>Pending</td>
                                        <td>Environmental Case</td>
                                        <td>Environmental Group</td>
                                        <td>Mining Company</td>
                                        <td>Environmental compliance</td>
                                        <td>2024-03-01</td>
                                        <td>
                                            <button class="btn btn-sm btn-warning" data-toggle="modal" data-target="#addCaseModal" data-mode="edit">Edit</button>
                                            <button class="btn btn-sm btn-danger">Delete</button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <!-- DataTable Pagination and Info will appear here -->
                    </div>
                </div>
            </div>

            <!-- Tab 3 -->
            <div class="tab-pane fade" id="tab3" role="tabpanel" aria-labelledby="tab3-tab">
                <div class="card shadow mb-4">
                    <div class="card-body">
                        <!-- Search + Buttons Row for Tab 3 -->
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <!-- Search on the left -->
                            <div class="d-flex align-items-center">
                                <label class="mr-2 mb-0">Search:</label>
                                <input type="search" class="form-control form-control-sm" id="customSearch3" placeholder="Search cases..." style="width: 200px;">
                            </div>

                            <!-- Buttons on the right -->
                            <div>
                                <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addCaseModal" data-mode="add">
                                    + Add Case
                                </button>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered table-sm custom-table" id="dataTable3" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>Case No.</th>
                                        <th>Case Status</th>
                                        <th>Type of Case</th>
                                        <th>Complainant Information</th>
                                        <th>Respondent Information</th>
                                        <th>Case Details</th>
                                        <th>Date filed</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>REG-001</td>
                                        <td>Active</td>
                                        <td>Inter-provincial Case</td>
                                        <td>Regional Authority</td>
                                        <td>Multi-Province Corp</td>
                                        <td>Cross-border trade dispute</td>
                                        <td>2024-01-30</td>
                                        <td>
                                            <button class="btn btn-sm btn-warning" data-toggle="modal" data-target="#addCaseModal" data-mode="edit">Edit</button>
                                            <button class="btn btn-sm btn-danger">Delete</button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>REG-002</td>
                                        <td>Closed</td>
                                        <td>Regional Policy Case</td>
                                        <td>Citizens Alliance</td>
                                        <td>Regional Government</td>
                                        <td>Policy implementation challenge</td>
                                        <td>2024-02-15</td>
                                        <td>
                                            <button class="btn btn-sm btn-warning" data-toggle="modal" data-target="#addCaseModal" data-mode="edit">Edit</button>
                                            <button class="btn btn-sm btn-danger">Delete</button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <!-- DataTable Pagination and Info will appear here -->
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
        <!-- Form -->
        <form id="caseForm" method="post" action=" {{ route('case.store') }}">
            @csrf
            @method('post')
            <!-- Hidden fields for edit mode -->
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
                <input type="date" class="form-control"  name="date_filed" id="dateFiled">
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
    
    // Initialize each table
    tables.table1 = $('#dataTable1').DataTable({
        "pageLength": 5,
        "lengthChange": false,
        "paging": true,
        "searching": false, // Disable default search to use custom
        "info": true,
        "dom": 'tip', // Remove default search box
        "order": [[0, "asc"]] // Default sorting
    });
    
    tables.table2 = $('#dataTable2').DataTable({
        "pageLength": 5,
        "lengthChange": false,
        "paging": true,
        "searching": false,
        "info": true,
        "dom": 'tip',
        "order": [[0, "asc"]]
    });
    
    tables.table3 = $('#dataTable3').DataTable({
        "pageLength": 5,
        "lengthChange": false,
        "paging": true,
        "searching": false,
        "info": true,
        "dom": 'tip',
        "order": [[0, "asc"]]
    });

    // Custom search functionality
    $('#customSearch1').on('keyup input change', function() {
        var searchValue = this.value;
        tables.table1.search(searchValue).draw();
    });
    
    $('#customSearch2').on('keyup input change', function() {
        var searchValue = this.value;
        tables.table2.search(searchValue).draw();
    });
    
    $('#customSearch3').on('keyup input change', function() {
        var searchValue = this.value;
        tables.table3.search(searchValue).draw();
    });

    // Clear search when switching tabs
    $('a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
        var targetTab = $(e.target).attr('href');
        
        // Clear all search boxes
        $('#customSearch1, #customSearch2, #customSearch3').val('');
        
        // Clear all table searches
        Object.values(tables).forEach(function(table) {
            table.search('').draw();
        });
    });

    // Modal handling for Add/Edit
    $('#addCaseModal').on('show.bs.modal', function(event) {
        var button = $(event.relatedTarget);
        var mode = button.data('mode') || 'add';
        var modal = $(this);
        
        // Update modal title
        modal.find('#addCaseModalLabel').text(mode === 'add' ? 'Add New Case' : 'Edit Case');

        if (mode === 'edit') {
            // For edit mode, you can populate the form with existing data
            // This would require additional AJAX call to get the specific case data
            var caseId = button.data('case-id');
            // You can implement AJAX here to fetch case details by ID if needed
        } else {
            // Reset form for add mode
            modal.find('#caseForm')[0].reset();
        }
    });

    // Auto-dismiss alerts after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);

    console.log('DataTables initialized successfully with database data');
});
</script>
@stop