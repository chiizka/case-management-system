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
            
        <!-- Tabs Content -->
<div class="tab-content mt-3" id="dataTableTabsContent">
    
    <!-- Tab 0: All Active Cases (KEEP EXISTING - Loads on page load) -->
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
                                                <button type="button" 
                                                        class="btn btn-danger btn-sm delete-btn" 
                                                        data-case-id="{{ $case->id }}" 
                                                        title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
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
                                <select class="form-control" id="overall_status" name="overall_status" required disabled>
                                    <option value="Active" selected>Active</option>
                                    <option value="Completed">Completed</option>
                                    <option value="Dismissed">Dismissed</option>
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
@endsection
@section('scripts')
<!-- DataTables plugins -->
<script src="{{ asset('vendor/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('vendor/datatables/dataTables.bootstrap4.min.js') }}"></script>

<script>$(document).ready(function() {
    console.log('jQuery version:', $.fn.jquery);
    console.log('DataTables available:', typeof $.fn.DataTable !== 'undefined');

    // Store all table instances
    var tables = {};
    
    // Track which tabs have been loaded
    var loadedTabs = {
        'tab0': true, // Tab 0 is loaded on page load
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
    };

    // Function to safely initialize a DataTable
    function initDataTable(tableId) {
        try {
            if ($(tableId).length === 0) {
                console.warn('Table not found:', tableId);
                return false;
            }
            
            // Check if table has actual data rows (not just "no records" message)
            const $tbody = $(tableId + ' tbody');
            const $rows = $tbody.find('tr');
            
            if ($rows.length === 0) {
                console.log('Table has no rows:', tableId);
                return false;
            }
            
            // Check if the only row is a "no records" message (has colspan)
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
            return true;
        } catch (error) {
            console.error('✗ Failed to initialize ' + tableId + ':', error);
            return false;
        }
    }

    // NEW: Function to load tab data via AJAX
    function loadTabData(tabId, tabNumber) {
        const $tabPane = $('#' + tabId);
        const $cardBody = $tabPane.find('.card-body');
        
        // Show loading indicator
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
                    // Replace loading indicator with actual content
                    $cardBody.html(response.html);
                    
                    // Mark tab as loaded
                    loadedTabs[tabId] = true;
                    
                    // Initialize DataTable for this tab
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

    // UPDATED: Tab switching with lazy loading
    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        var target = $(e.target).attr("href");
        var tableId = target.replace('#tab', '#dataTable');
        var tabId = target.replace('#', '');
        var tabNumber = tabId.replace('tab', '');
        
        console.log('Tab switched to:', target);
        
        // Skip Tab 0 (already loaded)
        if (tabId === 'tab0') {
            if (tables[tableId]) {
                tables[tableId].columns.adjust().draw(false);
            }
            return;
        }
        
        // Check if tab data has been loaded
        if (!loadedTabs[tabId]) {
            console.log('Loading data for:', tabId);
            loadTabData(tabId, tabNumber);
        } else {
            // Tab already loaded, just adjust columns
            console.log('Adjusting columns for:', tableId);
            if (tables[tableId]) {
                tables[tableId].columns.adjust().draw(false);
            }
        }
        
        // Adjust after a short delay
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

    // Custom search for each table
    $('#customSearch0').on('keyup input change', function() {
        if (tables['#dataTable0']) tables['#dataTable0'].search(this.value).draw();
    });
    $('#customSearch1').on('keyup input change', function() {
        if (tables['#dataTable1']) tables['#dataTable1'].search(this.value).draw();
    });
    $('#customSearch2').on('keyup input change', function() {
        if (tables['#dataTable2']) tables['#dataTable2'].search(this.value).draw();
    });
    $('#customSearch3').on('keyup input change', function() {
        if (tables['#dataTable3']) tables['#dataTable3'].search(this.value).draw();
    });
    $('#customSearch4').on('keyup input change', function() {
        if (tables['#dataTable4']) tables['#dataTable4'].search(this.value).draw();
    });
    $('#customSearch5').on('keyup input change', function() {
        if (tables['#dataTable5']) tables['#dataTable5'].search(this.value).draw();
    });
    $('#customSearch6').on('keyup input change', function() {
        if (tables['#dataTable6']) tables['#dataTable6'].search(this.value).draw();
    });
    $('#customSearch7').on('keyup input change', function() {
        if (tables['#dataTable7']) tables['#dataTable7'].search(this.value).draw();
    });

    // Handle active tab from session (after moveToNextStage)
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

    // Delete handler
    $(document).on('click', '.delete-btn', function(e) {
        e.preventDefault();
        console.log('Delete button clicked');
        
        const button = $(this);
        const caseId = button.data('case-id');
        const row = button.closest('tr');
        
        console.log('Case ID:', caseId);
        
        const csrfToken = $('meta[name="csrf-token"]').attr('content');
        console.log('CSRF Token:', csrfToken);
        
        if (!csrfToken) {
            showAlert('error', 'CSRF token not found. Please refresh the page.');
            return;
        }
        
        if (confirm('Are you sure you want to delete this case? This action cannot be undone.')) {
            console.log('User confirmed deletion');
            
            button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
            
            $.ajax({
                url: `/case/${caseId}`,
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
                    
                    row.fadeOut(300, function() {
                        $(this).remove();
                        
                        const tableBody = $('#dataTable0 tbody, #dataTable1 tbody').filter(':visible');
                        if (tableBody.find('tr:visible').length === 0) {
                            const colspan = tableBody.closest('table').find('thead th').length;
                            tableBody.html(
                                `<tr><td colspan="${colspan}" class="text-center">No records found.</td></tr>`
                            );
                        }
                    });
                    
                    showAlert('success', response.message || 'Record deleted successfully!');
                },
                error: function(xhr, status, error) {
                    console.log('=== ERROR RESPONSE ===');
                    console.log('Error occurred:', xhr, status, error);
                    console.log('Status Code:', xhr.status);
                    console.log('Response Text:', xhr.responseText);
                    console.log('Response Headers:', xhr.getAllResponseHeaders());
                    
                    button.prop('disabled', false).html('<i class="fas fa-trash"></i>');
                    
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
        },
        'tab3': {
            name: 'hearing',
            endpoint: '/hearing-process/',
            editBtnClass: '.edit-row-btn-hearing',
            saveBtnClass: '.save-btn-hearing',
            cancelBtnClass: '.cancel-btn-hearing',
            alertPrefix: 'tab3',
            fields: {
                'date_1st_mc_actual': { type: 'date' },
                'first_mc_pct': { type: 'text' },
                'status_1st_mc': {
                    type: 'select',
                    options: [
                        { value: '', text: 'Select Status' },
                        { value: 'Pending', text: 'Pending' },
                        { value: 'Ongoing', text: 'Ongoing' },
                        { value: 'Completed', text: 'Completed' }
                    ]
                },
                'date_2nd_last_mc': { type: 'date' },
                'second_last_mc_pct': { type: 'text' },
                'status_2nd_mc': {
                    type: 'select',
                    options: [
                        { value: '', text: 'Select Status' },
                        { value: 'Pending', text: 'Pending' },
                        { value: 'In Progress', text: 'In Progress' },
                        { value: 'Completed', text: 'Completed' }
                    ]
                },
                'case_folder_forwarded_to_ro': { type: 'text' },
                'complete_case_folder': {
                    type: 'select',
                    options: [
                        { value: 'N', text: 'No' },
                        { value: 'Y', text: 'Yes' }
                    ]
                }
            }
        },
        'tab4': {
            name: 'review-and-drafting',
            endpoint: '/review-and-drafting/',
            editBtnClass: '.edit-row-btn-review',
            saveBtnClass: '.save-btn-review',
            cancelBtnClass: '.cancel-btn-review',
            alertPrefix: 'tab4',
            fields: {
                'draft_order_type': { type: 'text' },
                'applicable_draft_order': {
                    type: 'select',
                    options: [
                        { value: 'Y', text: 'Yes' },
                        { value: 'N', text: 'No' }
                    ]
                },
                'po_pct': { type: 'number' },
                'aging_po_pct': { type: 'number' },
                'status_po_pct': {
                    type: 'select',
                    options: [
                        { value: '', text: 'Select Status' },
                        { value: 'Pending', text: 'Pending' },
                        { value: 'Ongoing', text: 'Ongoing' },
                        { value: 'Overdue', text: 'Overdue' },
                        { value: 'Completed', text: 'Completed' }
                    ]
                },
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
                'draft_order_tssd_reviewer': { type: 'text' }
            }
        },
        'tab5': {
            name: 'orders-and-disposition',
            endpoint: '/orders-and-disposition/',
            editBtnClass: '.edit-row-btn-orders',
            saveBtnClass: '.save-btn-orders',
            cancelBtnClass: '.cancel-btn-orders',
            alertPrefix: 'tab5',
            fields: {
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
                'pct_96_days': { type: 'number' },
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
                'date_of_order_actual': { type: 'date' },
                'released_date_actual': { type: 'date' }
            }
        },
        'tab6': {
            name: 'compliance-and-awards',
            endpoint: '/compliance-and-awards/',
            editBtnClass: '.edit-row-btn-compliance',
            saveBtnClass: '.save-btn-compliance',
            cancelBtnClass: '.cancel-btn-compliance',
            alertPrefix: 'tab6',
            fields: {
                'compliance_order_monetary_award': { type: 'number', step: '0.01' },
                'osh_penalty': { type: 'number', step: '0.01' },
                'affected_male': { type: 'number' },
                'affected_female': { type: 'number' },
                'first_order_dismissal_cnpc': {
                    type: 'select',
                    options: [
                        { value: '0', text: 'No' },
                        { value: '1', text: 'Yes' }
                    ]
                },
                'tavable_less_than_10_workers': {
                    type: 'select',
                    options: [
                        { value: '0', text: 'No' },
                        { value: '1', text: 'Yes' }
                    ]
                },
                'with_deposited_monetary_claims': {
                    type: 'select',
                    options: [
                        { value: '0', text: 'No' },
                        { value: '1', text: 'Yes' }
                    ]
                },
                'amount_deposited': { type: 'number', step: '0.01' },
                'with_order_payment_notice': {
                    type: 'select',
                    options: [
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
                'updated_ticked_in_mis': {
                    type: 'select',
                    options: [
                        { value: '0', text: 'No' },
                        { value: '1', text: 'Yes' }
                    ]
                },
                'second_order_drafter': { type: 'text' },
                'date_received_by_drafter_ct_cnpc': { type: 'date' }
            }
        }, 
        'tab7': {
            name: 'appeals-and-resolution',
            endpoint: '/appeals-and-resolution/',
            editBtnClass: '.edit-row-btn-appeals',
            saveBtnClass: '.save-btn-appeals',
            cancelBtnClass: '.cancel-btn-appeals',
            alertPrefix: 'tab7',
            fields: {
                'date_returned_case_mgmt': { type: 'date' },
                'review_ct_cnpc': { type: 'text' },
                'date_received_drafter_finalization_2nd': { type: 'date' },
                'date_returned_case_mgmt_signature_2nd': { type: 'date' },
                'date_order_2nd_cnpc': { type: 'date' },
                'released_date_2nd_cnpc': { type: 'date' },
                'date_forwarded_malsu': { type: 'date' },
                'motion_reconsideration_date': { type: 'date' },
                'date_received_malsu': { type: 'date' },
                'date_resolution_mr': { type: 'date' },
                'released_date_resolution_mr': { type: 'date' },
                'date_appeal_received_records': { type: 'date' }
            }
        }
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
            let displayValue = responseData[field];
            
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
</script>
@endsection