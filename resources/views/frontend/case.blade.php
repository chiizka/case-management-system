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

            <!-- Tab 1 -->
            <div class="tab-pane fade show active" id="tab1" role="tabpanel" aria-labelledby="tab1-tab">
                <div class="card shadow mb-4">
                    <div class="card-body">

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
                                    <tr>
                                        <td>CASE-001</td>
                                        <td>Active</td>
                                        <td>Civil Case</td>
                                        <td>John Doe</td>
                                        <td>Jane Smith</td>
                                        <td>Property dispute case</td>
                                        <td>2024-01-15</td>
                                        <td>
                                            <button class="btn btn-sm btn-warning" data-toggle="modal" data-target="#addCaseModal" data-mode="edit">Edit</button>
                                            <button class="btn btn-sm btn-danger">Delete</button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>CASE-002</td>
                                        <td>Pending</td>
                                        <td>Criminal Case</td>
                                        <td>Mary Johnson</td>
                                        <td>Bob Wilson</td>
                                        <td>Theft case investigation</td>
                                        <td>2024-02-10</td>
                                        <td>
                                            <button class="btn btn-sm btn-warning" data-toggle="modal" data-target="#addCaseModal" data-mode="edit">Edit</button>
                                            <button class="btn btn-sm btn-danger">Delete</button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>CASE-003</td>
                                        <td>Closed</td>
                                        <td>Family Case</td>
                                        <td>Alice Brown</td>
                                        <td>Charlie Davis</td>
                                        <td>Custody arrangement</td>
                                        <td>2024-01-20</td>
                                        <td>
                                            <button class="btn btn-sm btn-warning" data-toggle="modal" data-target="#addCaseModal" data-mode="edit">Edit</button>
                                            <button class="btn btn-sm btn-danger">Delete</button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>CASE-004</td>
                                        <td>Active</td>
                                        <td>Commercial Case</td>
                                        <td>Tech Corp Ltd</td>
                                        <td>Retail Inc</td>
                                        <td>Contract breach dispute</td>
                                        <td>2024-03-05</td>
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
        <form id="caseForm">
            <!-- Hidden fields for edit mode -->
            <input type="hidden" id="rowIndex" value="">
            <input type="hidden" id="tableId" value="">
            
            <div class="form-group">
                <label for="caseNumber">Case No.</label>
                <input type="text" class="form-control" id="caseNumber" placeholder="Enter case number">
            </div>
            <div class="form-group">
                <label for="caseStatus">Case Status</label>
                <select class="form-control" id="caseStatus">
                    <option value="">Select status</option>
                    <option value="Active">Active</option>
                    <option value="Pending">Pending</option>
                    <option value="Closed">Closed</option>
                </select>
            </div>
            <div class="form-group">
                <label for="caseType">Type of Case</label>
                <input type="text" class="form-control" id="caseType" placeholder="Enter type of case">
            </div>
            <div class="form-group">
                <label for="complainantInfo">Complainant Information</label>
                <textarea class="form-control" id="complainantInfo" rows="3" placeholder="Enter complainant information"></textarea>
            </div>
            <div class="form-group">
                <label for="respondentInfo">Respondent Information</label>
                <textarea class="form-control" id="respondentInfo" rows="3" placeholder="Enter respondent information"></textarea>
            </div>
            <div class="form-group">
                <label for="caseDetails">Case Details</label>
                <textarea class="form-control" id="caseDetails" rows="3" placeholder="Enter case details"></textarea>
            </div>
            <div class="form-group">
                <label for="dateFiled">Date Filed</label>
                <input type="date" class="form-control" id="dateFiled">
            </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" id="saveCaseBtn">Save Case</button>
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
        console.log('Searching table1 for: ' + searchValue); // Debug log
    });
    
    $('#customSearch2').on('keyup input change', function() {
        var searchValue = this.value;
        tables.table2.search(searchValue).draw();
        console.log('Searching table2 for: ' + searchValue); // Debug log
    });
    
    $('#customSearch3').on('keyup input change', function() {
        var searchValue = this.value;
        tables.table3.search(searchValue).draw();
        console.log('Searching table3 for: ' + searchValue); // Debug log
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

    // Modal handling
    $('#addCaseModal').on('show.bs.modal', function(event) {
        var button = $(event.relatedTarget);
        var mode = button.data('mode') || 'add';
        var modal = $(this);
        
        // Update modal title and button text
        modal.find('#addCaseModalLabel').text(mode === 'add' ? 'Add New Case' : 'Edit Case');
        modal.find('#saveCaseBtn').text(mode === 'add' ? 'Save Case' : 'Update Case');

        if (mode === 'edit') {
            // Get data from the row for editing
            var row = button.closest('tr');
            var tableElement = row.closest('table');
            var tableId = tableElement.attr('id');
            var tableName = 'table' + tableId.replace('dataTable', '');
            var table = tables[tableName];
            
            if (table) {
                var data = table.row(row).data();
                var rowIndex = table.row(row).index();
                
                // Populate form with existing data
                modal.find('#rowIndex').val(rowIndex);
                modal.find('#tableId').val(tableId);
                modal.find('#caseNumber').val(data[0]);
                modal.find('#caseStatus').val(data[1]);
                modal.find('#caseType').val(data[2]);
                modal.find('#complainantInfo').val(data[3]);
                modal.find('#respondentInfo').val(data[4]);
                modal.find('#caseDetails').val(data[5]);
                modal.find('#dateFiled').val(data[6]);
            }
        } else {
            // Reset form for add mode
            modal.find('#caseForm')[0].reset();
            modal.find('#rowIndex').val('');
            // Set default table based on active tab
            var activeTab = $('.nav-tabs .nav-link.active').attr('href');
            var defaultTable = 'dataTable1';
            if (activeTab === '#tab2') defaultTable = 'dataTable2';
            if (activeTab === '#tab3') defaultTable = 'dataTable3';
            modal.find('#tableId').val(defaultTable);
        }
    });

    // Save case functionality
    $('#saveCaseBtn').on('click', function() {
        var modal = $('#addCaseModal');
        var mode = modal.find('#saveCaseBtn').text() === 'Save Case' ? 'add' : 'edit';
        var tableId = modal.find('#tableId').val() || 'dataTable1';
        var tableName = 'table' + tableId.replace('dataTable', '');
        var table = tables[tableName];
        
        if (!table) {
            alert('Error: Table not found');
            return;
        }

        // Get current page to maintain pagination
        var currentPage = table.page.info().page;

        // Collect form data
        var rowData = [
            modal.find('#caseNumber').val(),
            modal.find('#caseStatus').val(),
            modal.find('#caseType').val(),
            modal.find('#complainantInfo').val(),
            modal.find('#respondentInfo').val(),
            modal.find('#caseDetails').val(),
            modal.find('#dateFiled').val(),
            '<button class="btn btn-sm btn-warning" data-toggle="modal" data-target="#addCaseModal" data-mode="edit">Edit</button> ' +
            '<button class="btn btn-sm btn-danger delete-btn">Delete</button>'
        ];

        if (mode === 'edit') {
            // Update existing row
            var rowIndex = parseInt(modal.find('#rowIndex').val());
            table.row(rowIndex).data(rowData).draw(false);
        } else {
            // Add new row
            table.row.add(rowData).draw(false);
        }

        // Return to the same page
        table.page(currentPage).draw('page');
        modal.modal('hide');
        
        // Show success message
        alert(mode === 'add' ? 'Case added successfully!' : 'Case updated successfully!');
    });

    // Delete functionality with event delegation
    function setupDeleteHandler(tableId, table) {
        $(tableId).on('click', '.delete-btn, .btn-danger', function(e) {
            e.preventDefault();
            if (confirm('Are you sure you want to delete this case?')) {
                var currentPage = table.page.info().page;
                var row = table.row($(this).parents('tr'));
                row.remove().draw(false);
                
                // Stay on current page if possible
                var info = table.page.info();
                if (currentPage >= info.pages && info.pages > 0) {
                    table.page(info.pages - 1).draw('page');
                } else {
                    table.page(currentPage).draw('page');
                }
                
                alert('Case deleted successfully!');
            }
        });
    }

    // Setup delete handlers for all tables
    setupDeleteHandler('#dataTable1', tables.table1);
    setupDeleteHandler('#dataTable2', tables.table2);
    setupDeleteHandler('#dataTable3', tables.table3);

    console.log('DataTables initialized successfully with search functionality');
});
</script>

@stop