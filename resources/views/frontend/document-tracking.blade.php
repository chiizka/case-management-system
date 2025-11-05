@extends('frontend.layouts.app')
@section('content')

<style>
/* Modern card styling */
.location-card {
    transition: all 0.3s ease;
    border-left: 4px solid #4e73df;
    margin-bottom: 1rem;
}

.location-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

/* Location badges with colors */
.location-badge {
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-weight: 600;
    font-size: 0.85rem;
    display: inline-block;
}

.loc-records { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
.loc-malsu { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; }
.loc-regionaldirector { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white; }
.loc-laborarbiter { background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); color: white; }
.loc-hearingofficer { background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); color: white; }
.loc-other { background: linear-gradient(135deg, #30cfd0 0%, #330867 100%); color: white; }

/* Timeline styling */
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 10px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #e3e6f0;
}

.timeline-item {
    position: relative;
    margin-bottom: 1.5rem;
}

.timeline-item::before {
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

/* Search and filter bar */
.filter-bar {
    background: #f8f9fc;
    padding: 1.5rem;
    border-radius: 0.35rem;
    margin-bottom: 1.5rem;
}

/* Stats cards */
.stat-card {
    border-left: 4px solid;
    padding: 1rem;
    background: white;
    border-radius: 0.35rem;
    cursor: pointer;
    transition: all 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 6px 16px rgba(0,0,0,0.1);
}

.stat-card.active {
    border-color: #667eea;
    background: #f8f9fc;
}

.stat-card.records { border-color: #667eea; }
.stat-card.malsu { border-color: #f5576c; }
.stat-card.regional { border-color: #00f2fe; }
.stat-card.labor { border-color: #38f9d7; }

/* Compact table styling */
.tracking-table {
    font-size: 0.9rem;
}

.tracking-table th {
    background: #f8f9fc;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.75rem;
    letter-spacing: 0.5px;
    color: #858796;
}

.tracking-table td {
    vertical-align: middle;
}

/* Action buttons */
.btn-track {
    padding: 0.25rem 0.75rem;
    font-size: 0.8rem;
}

/* Modal improvements */
.modal-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.modal-header .close {
    color: white;
    opacity: 0.8;
}

.modal-header .close:hover {
    opacity: 1;
}

/* Form styling */
.form-control:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
}

/* Quick filters */
.quick-filter {
    cursor: pointer;
    transition: all 0.2s;
    border: 2px solid transparent;
}

.quick-filter:hover {
    border-color: #667eea;
    transform: scale(1.02);
}

.quick-filter.active {
    border-color: #667eea;
    background: #f8f9fc;
}

/* Status indicators */
.status-indicator {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    display: inline-block;
    margin-right: 0.5rem;
}

.status-active { background: #1cc88a; }
.status-pending { background: #f6c23e; }
.status-archived { background: #858796; }

.status-row {
    display: flex;
    align-items: center;
}
</style>

<div id="content">
    <div class="container-fluid">
        
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0 text-gray-800">
                    <i class="fas fa-map-marker-alt text-primary"></i> Document Location Tracking
                </h1>
                <p class="text-muted small mb-0">Track physical case documents across offices</p>
            </div>
            <button class="btn btn-primary" data-toggle="modal" data-target="#transferModal">
                <i class="fas fa-exchange-alt"></i> Transfer Document
            </button>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card stat-card records quick-filter" data-location="Records">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-xs font-weight-bold text-uppercase mb-1">Records</div>
                                <div class="h5 mb-0 font-weight-bold">{{ $locationCounts['Records'] ?? 0 }}</div>
                            </div>
                            <div class="text-muted">
                                <i class="fas fa-archive fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card stat-card malsu quick-filter" data-location="MALSU">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-xs font-weight-bold text-uppercase mb-1">MALSU</div>
                                <div class="h5 mb-0 font-weight-bold">{{ $locationCounts['MALSU'] ?? 0 }}</div>
                            </div>
                            <div class="text-muted">
                                <i class="fas fa-balance-scale fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card stat-card regional quick-filter" data-location="Regional Director">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-xs font-weight-bold text-uppercase mb-1">Regional Dir.</div>
                                <div class="h5 mb-0 font-weight-bold">{{ $locationCounts['Regional Director'] ?? 0 }}</div>
                            </div>
                            <div class="text-muted">
                                <i class="fas fa-user-tie fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card stat-card labor quick-filter" data-location="Labor Arbiter">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-xs font-weight-bold text-uppercase mb-1">Labor Arbiter</div>
                                <div class="h5 mb-0 font-weight-bold">{{ $locationCounts['Labor Arbiter'] ?? 0 }}</div>
                            </div>
                            <div class="text-muted">
                                <i class="fas fa-gavel fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="filter-bar">
            <div class="row align-items-end">
                <div class="col-md-4 mb-2 mb-md-0">
                    <label class="small font-weight-bold text-uppercase mb-1">Search</label>
                    <input type="text" class="form-control" id="searchInput" placeholder="Case No., Establishment...">
                </div>
                <div class="col-md-3 mb-2 mb-md-0">
                    <label class="small font-weight-bold text-uppercase mb-1">Current Location</label>
                    <select class="form-control" id="locationFilter">
                        <option value="">All Locations</option>
                        <option value="Records">Records</option>
                        <option value="MALSU">MALSU</option>
                        <option value="Regional Director">Regional Director</option>
                        <option value="Labor Arbiter">Labor Arbiter</option>
                        <option value="Hearing Officer">Hearing Officer</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="col-md-3 mb-2 mb-md-0">
                    <label class="small font-weight-bold text-uppercase mb-1">Status</label>
                    <select class="form-control" id="statusFilter">
                        <option value="">All Status</option>
                        <option value="Active">Active</option>
                        <option value="Pending Transfer">Pending Transfer</option>
                        <option value="Archived">Archived</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-secondary btn-block" id="clearFilters">
                        <i class="fas fa-redo"></i> Reset
                    </button>
                </div>
            </div>
        </div>

        <!-- Alert Messages -->
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

        <!-- Document Tracking Table -->
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">Document Locations</h6>
                <span class="badge badge-primary badge-pill" id="totalCount">{{ $documents->count() ?? 0 }} Documents</span>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover tracking-table" id="trackingTable">
                        <thead>
                            <tr>
                                <th>Case No.</th>
                                <th>Establishment</th>
                                <th>Current Location</th>
                                <th>Received By</th>
                                <th>Date Received</th>
                                <th>Days at Location</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($documents ?? [] as $doc)
                            <tr data-case-id="{{ $doc->case_id }}">
                                <td class="font-weight-bold text-primary">{{ $doc->case->case_no ?? 'N/A' }}</td>
                                <td>
                                    <div class="text-truncate" style="max-width: 200px;" title="{{ $doc->case->establishment_name ?? 'N/A' }}">
                                        {{ $doc->case->establishment_name ?? 'N/A' }}
                                    </div>
                                </td>
                                <td>
                                    <span class="location-badge loc-{{ strtolower(str_replace(' ', '', $doc->current_location)) }}">
                                        {{ $doc->current_location }}
                                    </span>
                                </td>
                                <td>{{ $doc->received_by ?? '-' }}</td>
                                <td>{{ $doc->date_received ? \Carbon\Carbon::parse($doc->date_received)->format('M d, Y') : '-' }}</td>
                                <td>
                                    @if($doc->date_received)
                                        @php
                                            $days = floor(\Carbon\Carbon::parse($doc->date_received)->diffInDays(now()));
                                            $badgeClass = $days > 30 ? 'danger' : ($days > 14 ? 'warning' : 'success');
                                        @endphp
                                        <span class="badge badge-{{ $badgeClass }}">{{ $days }} days</span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    <div class="status-row">
                                        <span class="status-indicator status-{{ strtolower(str_replace(' ', '', $doc->status ?? 'active')) }}"></span>
                                        {{ $doc->status ?? 'Active' }}
                                    </div>
                                </td>
                                <td>
                                    <button class="btn btn-info btn-sm btn-track view-history-btn" 
                                            data-doc-id="{{ $doc->id }}"
                                            title="View History">
                                        <i class="fas fa-history"></i>
                                    </button>
                                    <button class="btn btn-warning btn-sm btn-track transfer-btn" 
                                            data-doc-id="{{ $doc->id }}"
                                            data-case-id="{{ $doc->case_id }}"
                                            data-case-no="{{ $doc->case->case_no ?? 'N/A' }}"
                                            title="Transfer">
                                        <i class="fas fa-exchange-alt"></i>
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                    No documents being tracked yet. Click "Transfer Document" to start tracking.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Transfer Document Modal -->
<div class="modal fade" id="transferModal" tabindex="-1" role="dialog" aria-labelledby="transferModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="transferModalLabel">
                    <i class="fas fa-exchange-alt"></i> Transfer Document
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="transferForm">
                @csrf
                <input type="hidden" name="document_id" id="document_id">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Case <span class="text-danger">*</span></label>
                                <select class="form-control" name="case_id" id="case_id" required>
                                    <option value="">Select Case</option>
                                    @foreach($cases ?? [] as $case)
                                        <option value="{{ $case->id }}">
                                            {{ $case->case_no }} - {{ Str::limit($case->establishment_name, 30) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Transfer To <span class="text-danger">*</span></label>
                                <select class="form-control" name="current_location" id="current_location" required>
                                    <option value="">Select Location</option>
                                    <option value="Records">Records</option>
                                    <option value="MALSU">MALSU</option>
                                    <option value="Regional Director">Regional Director</option>
                                    <option value="Labor Arbiter">Labor Arbiter</option>
                                    <option value="Hearing Officer">Hearing Officer</option>
                                    <option value="Other">Other (Specify)</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Received By <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="received_by" id="received_by" placeholder="Name of receiver" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Date Received <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" name="date_received" id="date_received" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-group" id="otherLocationGroup" style="display: none;">
                        <label class="font-weight-bold">Specify Other Location <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="other_location" id="other_location" placeholder="Enter location">
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold">Notes/Remarks</label>
                        <textarea class="form-control" name="notes" id="notes" rows="3" placeholder="Optional notes about this transfer..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-check"></i> Confirm Transfer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- History Modal -->
<div class="modal fade" id="historyModal" tabindex="-1" role="dialog" aria-labelledby="historyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="historyModalLabel">
                    <i class="fas fa-history"></i> Document Transfer History
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <strong>Case:</strong> <span id="historyCaseNo"></span><br>
                    <strong>Establishment:</strong> <span id="historyEstablishment"></span>
                </div>
                <hr>
                <div class="timeline" id="historyTimeline">
                    <!-- Timeline items will be loaded here via AJAX -->
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

@endsection

@section('scripts')
<script>
$(document).ready(function() {
    
    // Set today's date as default
    $('#date_received').val(new Date().toISOString().split('T')[0]);

    // Show/hide other location field
    $('#current_location').on('change', function() {
        if ($(this).val() === 'Other') {
            $('#otherLocationGroup').slideDown();
            $('#other_location').prop('required', true);
        } else {
            $('#otherLocationGroup').slideUp();
            $('#other_location').prop('required', false);
        }
    });

    // Quick filter cards
    $('.quick-filter').on('click', function() {
        const location = $(this).data('location');
        $('.quick-filter').removeClass('active');
        $(this).addClass('active');
        $('#locationFilter').val(location).trigger('change');
    });

    // Search functionality
    $('#searchInput').on('keyup', function() {
        filterTable();
    });

    $('#locationFilter, #statusFilter').on('change', function() {
        filterTable();
    });

    function filterTable() {
        const searchTerm = $('#searchInput').val().toLowerCase();
        const location = $('#locationFilter').val();
        const status = $('#statusFilter').val();

        $('#trackingTable tbody tr').each(function() {
            const row = $(this);
            const caseNo = row.find('td:eq(0)').text().toLowerCase();
            const establishment = row.find('td:eq(1)').text().toLowerCase();
            const rowLocation = row.find('.location-badge').text().trim();
            const rowStatus = row.find('.status-row').text().trim();

            const matchesSearch = caseNo.includes(searchTerm) || establishment.includes(searchTerm);
            const matchesLocation = !location || rowLocation === location;
            const matchesStatus = !status || rowStatus.includes(status);

            if (matchesSearch && matchesLocation && matchesStatus) {
                row.show();
            } else {
                row.hide();
            }
        });

        updateVisibleCount();
    }

    function updateVisibleCount() {
        const visibleCount = $('#trackingTable tbody tr:visible').length;
        $('#totalCount').text(visibleCount + ' Documents');
    }

    // Clear filters
    $('#clearFilters').on('click', function() {
        $('#searchInput').val('');
        $('#locationFilter').val('');
        $('#statusFilter').val('');
        $('.quick-filter').removeClass('active');
        filterTable();
    });

    // Transfer form submission
    $('#transferForm').on('submit', function(e) {
        e.preventDefault();
        
        const formData = $(this).serialize();
        const url = $('#document_id').val() ? '/documents/update' : '/documents/transfer';

        $.ajax({
            url: url,
            method: 'POST',
            data: formData,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                $('#transferModal').modal('hide');
                showAlert(response.message || 'Document transferred successfully!', 'success');
                setTimeout(() => location.reload(), 1500);
            },
            error: function(xhr) {
                let errorMsg = 'Failed to transfer document.';
                if (xhr.responseJSON?.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                showAlert(errorMsg, 'danger');
            }
        });
    });

    // Transfer button click
    $(document).on('click', '.transfer-btn', function() {
        const docId = $(this).data('doc-id');
        const caseId = $(this).data('case-id');
        const caseNo = $(this).data('case-no');
        
        $('#document_id').val(docId);
        $('#case_id').val(caseId);
        $('#transferModalLabel').html('<i class="fas fa-exchange-alt"></i> Transfer Document - ' + caseNo);
        $('#transferModal').modal('show');
    });

    // View history button
    $(document).on('click', '.view-history-btn', function() {
        const docId = $(this).data('doc-id');
        
        $('#historyModal').modal('show');
        $('#historyTimeline').html(`
            <div class="text-center py-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="sr-only">Loading...</span>
                </div>
            </div>
        `);
        
        // Load history via AJAX
        $.ajax({
            url: '/documents/' + docId + '/history',
            method: 'GET',
            success: function(response) {
                $('#historyCaseNo').text(response.case_no);
                $('#historyEstablishment').text(response.establishment);
                
                let timelineHtml = '';
                response.history.forEach((item, index) => {
                    timelineHtml += `
                        <div class="timeline-item">
                            <div class="card mb-0">
                                <div class="card-body py-2">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <strong class="text-primary">${item.location}</strong>
                                            <div class="small text-muted">
                                                Received by: ${item.received_by}<br>
                                                ${item.notes ? 'Notes: ' + item.notes : ''}
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <div class="small font-weight-bold">${item.date}</div>
                                            <div class="small text-muted">${item.time_ago}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                });
                
                $('#historyTimeline').html(timelineHtml);
            },
            error: function() {
                $('#historyTimeline').html('<div class="alert alert-danger">Failed to load history</div>');
            }
        });
    });

    // Reset modal on close
    $('#transferModal').on('hidden.bs.modal', function() {
        $('#transferForm')[0].reset();
        $('#document_id').val('');
        $('#otherLocationGroup').hide();
        $('#transferModalLabel').html('<i class="fas fa-exchange-alt"></i> Transfer Document');
        $('#date_received').val(new Date().toISOString().split('T')[0]);
    });

});

function showAlert(message, type) {
    const alertId = type === 'success' ? 'success-alert' : 'error-alert';
    const messageId = type === 'success' ? 'success-message' : 'error-message';
    
    $('#' + messageId).text(message);
    $('#' + alertId).removeClass('fade').addClass('show').show();
    
    setTimeout(() => {
        $('#' + alertId).removeClass('show').addClass('fade');
    }, 5000);
}

function hideAlert(alertId) {
    $('#' + alertId).removeClass('show').addClass('fade');
}
</script>
@endsection