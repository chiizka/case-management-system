@extends('frontend.layouts.app')
@section('content')

<style>
/* Role-based badges */
.role-badge {
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-weight: 600;
    font-size: 0.85rem;
    display: inline-block;
}

.role-admin { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
.role-malsu { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; }
.role-case_management { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white; }
.role-records { background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); color: white; }

/* Province role badges - different shades of blue/teal */
.role-province_albay { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
.role-province_camarines_sur { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white; }
.role-province_camarines_norte { background: linear-gradient(135deg, #5f72bd 0%, #9b23ea 100%); color: white; }
.role-province_catanduanes { background: linear-gradient(135deg, #89f7fe 0%, #66a6ff 100%); color: white; }
.role-province_masbate { background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%); color: #333; }
.role-province_sorsogon { background: linear-gradient(135deg, #30cfd0 0%, #330867 100%); color: white; }

/* Status badges */
.status-badge {
    padding: 0.35rem 0.75rem;
    border-radius: 12px;
    font-weight: 600;
    font-size: 0.75rem;
}

.status-received { background: #1cc88a; color: white; }
.status-pending { background: #f6c23e; color: white; }
.status-transferred { background: #36b9cc; color: white; }

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

/* Filter bar */
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

.stat-card.admin { border-color: #667eea; }
.stat-card.malsu { border-color: #f5576c; }
.stat-card.case-mgmt { border-color: #00f2fe; }
.stat-card.records { border-color: #38f9d7; }
.stat-card.province { border-color: #4facfe; }

/* Table styling */
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

/* Modal */
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

/* Tabs */
.nav-tabs .nav-link {
    color: #858796;
    font-weight: 600;
}

.nav-tabs .nav-link.active {
    color: #4e73df;
    border-bottom: 3px solid #4e73df;
}

/* Pending badge pulse */
.pending-pulse {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { opacity: 1; }
    50% { opacity: 0.6; }
    100% { opacity: 1; }
}

/* Receive button highlight */
.btn-receive {
    background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
    border: none;
    color: white;
    font-weight: 600;
}

.btn-receive:hover {
    background: linear-gradient(135deg, #38f9d7 0%, #43e97b 100%);
    color: white;
    transform: scale(1.05);
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
                <p class="text-muted small mb-0">Track physical case documents across departments</p>
            </div>
            @if(Auth::user()->isAdmin())
                <button class="btn btn-primary" data-toggle="modal" data-target="#transferModal">
                    <i class="fas fa-exchange-alt"></i> Transfer Document
                </button>
            @endif
        </div>

        @if(Auth::user()->isAdmin())
        <div class="row mb-4">
            <div class="col-xl-2 col-md-4 mb-3">
                <div class="card stat-card admin">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-xs font-weight-bold text-uppercase mb-1">Admin</div>
                                <div class="h5 mb-0 font-weight-bold">{{ $roleCounts['admin'] ?? 0 }}</div>
                            </div>
                            <div class="text-muted">
                                <i class="fas fa-user-shield fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-2 col-md-4 mb-3">
                <div class="card stat-card malsu">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-xs font-weight-bold text-uppercase mb-1">MALSU</div>
                                <div class="h5 mb-0 font-weight-bold">{{ $roleCounts['malsu'] ?? 0 }}</div>
                            </div>
                            <div class="text-muted">
                                <i class="fas fa-balance-scale fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-2 col-md-4 mb-3">
                <div class="card stat-card case-mgmt">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-xs font-weight-bold text-uppercase mb-1">Case Mgmt</div>
                                <div class="h5 mb-0 font-weight-bold">{{ $roleCounts['case_management'] ?? 0 }}</div>
                            </div>
                            <div class="text-muted">
                                <i class="fas fa-folder-open fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-2 col-md-4 mb-3">
                <div class="card stat-card records">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-xs font-weight-bold text-uppercase mb-1">Records</div>
                                <div class="h5 mb-0 font-weight-bold">{{ $roleCounts['records'] ?? 0 }}</div>
                            </div>
                            <div class="text-muted">
                                <i class="fas fa-file-alt fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Province Stats Cards -->
            <div class="col-xl-2 col-md-4 mb-3">
                <div class="card stat-card province">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-xs font-weight-bold text-uppercase mb-1">Albay</div>
                                <div class="h5 mb-0 font-weight-bold">{{ $roleCounts['province_albay'] ?? 0 }}</div>
                            </div>
                            <div class="text-muted">
                                <i class="fas fa-building fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-2 col-md-4 mb-3">
                <div class="card stat-card province">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-xs font-weight-bold text-uppercase mb-1">Cam. Sur</div>
                                <div class="h5 mb-0 font-weight-bold">{{ $roleCounts['province_camarines_sur'] ?? 0 }}</div>
                            </div>
                            <div class="text-muted">
                                <i class="fas fa-building fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-2 col-md-4 mb-3">
                <div class="card stat-card province">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-xs font-weight-bold text-uppercase mb-1">Cam. Norte</div>
                                <div class="h5 mb-0 font-weight-bold">{{ $roleCounts['province_camarines_norte'] ?? 0 }}</div>
                            </div>
                            <div class="text-muted">
                                <i class="fas fa-building fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-2 col-md-4 mb-3">
                <div class="card stat-card province">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-xs font-weight-bold text-uppercase mb-1">Catanduanes</div>
                                <div class="h5 mb-0 font-weight-bold">{{ $roleCounts['province_catanduanes'] ?? 0 }}</div>
                            </div>
                            <div class="text-muted">
                                <i class="fas fa-building fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-2 col-md-4 mb-3">
                <div class="card stat-card province">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-xs font-weight-bold text-uppercase mb-1">Masbate</div>
                                <div class="h5 mb-0 font-weight-bold">{{ $roleCounts['province_masbate'] ?? 0 }}</div>
                            </div>
                            <div class="text-muted">
                                <i class="fas fa-building fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-2 col-md-4 mb-3">
                <div class="card stat-card province">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-xs font-weight-bold text-uppercase mb-1">Sorsogon</div>
                                <div class="h5 mb-0 font-weight-bold">{{ $roleCounts['province_sorsogon'] ?? 0 }}</div>
                            </div>
                            <div class="text-muted">
                                <i class="fas fa-building fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

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

        <!-- Tabs -->
        <ul class="nav nav-tabs mb-3" id="documentTabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link" id="my-docs-tab" data-toggle="tab" href="#myDocs" role="tab">
                    <i class="fas fa-folder"></i> My Documents
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link active" id="pending-tab" data-toggle="tab" href="#pending" role="tab">
                    <i class="fas fa-clock"></i> Pending Receipts
                    @if($pendingDocuments->count() > 0)
                        <span class="badge badge-warning ml-2">{{ $pendingDocuments->count() }}</span>
                    @endif
                </a>
            </li>
            @if(Auth::user()->isAdmin())
            <li class="nav-item">
                <a class="nav-link" id="all-docs-tab" data-toggle="tab" href="#allDocs" role="tab">
                    <i class="fas fa-list"></i> All Documents
                </a>
            </li>
            @endif
        </ul>

        <div class="tab-content" id="documentTabsContent">
            
            <!-- Pending Receipts Tab -->
            <div class="tab-pane fade show active" id="pending" role="tabpanel">
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-warning">
                            <i class="fas fa-clock"></i> Pending Receipts for {{ App\Helpers\RoleHelper::getRoleDisplayName() }}
                        </h6>
                        <span class="badge badge-warning badge-pill">{{ $pendingDocuments->count() }} Pending</span>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover tracking-table" id="pendingTable">
                                <thead>
                                    <tr>
                                        <th>Case No.</th>
                                        <th>Establishment</th>
                                        <th>Transferred By</th>
                                        <th>Transferred At</th>
                                        <th>Days Waiting</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($pendingDocuments as $doc)
                                    <tr>
                                        <td class="font-weight-bold text-primary">{{ $doc->case->case_no ?? 'N/A' }}</td>
                                        <td>
                                            <div class="text-truncate" style="max-width: 200px;" title="{{ $doc->case->establishment_name ?? 'N/A' }}">
                                                {{ $doc->case->establishment_name ?? 'N/A' }}
                                            </div>
                                        </td>
                                        <td>
                                            @if($doc->transferredBy)
                                                {{ $doc->transferredBy->fname }} {{ $doc->transferredBy->lname }}
                                            @else
                                                System
                                            @endif
                                        </td>
                                        <td>{{ $doc->transferred_at ? $doc->transferred_at->format('M d, Y h:i A') : 'N/A' }}</td>
                                        <td>
                                            @if($doc->transferred_at)
                                                @php
                                                    $days = floor($doc->transferred_at->diffInDays(now()));
                                                    $badgeClass = $days > 7 ? 'danger' : ($days > 3 ? 'warning' : 'success');
                                                @endphp
                                                <span class="badge badge-{{ $badgeClass }} pending-pulse">{{ $days }} days</span>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            <span class="status-badge status-pending">{{ $doc->status }}</span>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-receive receive-btn" 
                                                    data-doc-id="{{ $doc->id }}"
                                                    data-case-no="{{ $doc->case->case_no ?? 'N/A' }}"
                                                    title="Receive Document">
                                                <i class="fas fa-check"></i> Receive
                                            </button>
                                            <button class="btn btn-info btn-sm view-history-btn" 
                                                    data-doc-id="{{ $doc->id }}"
                                                    title="View History">
                                                <i class="fas fa-history"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">
                                            <i class="fas fa-check-circle fa-3x mb-3 d-block text-success"></i>
                                            No pending documents. All caught up!
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- My Documents Tab -->
            <div class="tab-pane fade" id="myDocs" role="tabpanel">
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-primary">My Received Documents</h6>
                        <span class="badge badge-primary badge-pill">{{ $myDocuments->count() }} Documents</span>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover tracking-table" id="myDocsTable">
                                <thead>
                                    <tr>
                                        <th>Case No.</th>
                                        <th>Establishment</th>
                                        <th>Department</th>
                                        <th>Received At</th>
                                        <th>Days Here</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($myDocuments as $doc)
                                    <tr>
                                        <td class="font-weight-bold text-primary">{{ $doc->case->case_no ?? 'N/A' }}</td>
                                        <td>
                                            <div class="text-truncate" style="max-width: 200px;" title="{{ $doc->case->establishment_name ?? 'N/A' }}">
                                                {{ $doc->case->establishment_name ?? 'N/A' }}
                                            </div>
                                        </td>
                                        <td>
                                            <span class="role-badge role-{{ $doc->current_role }}">
                                                {{ $doc->getRoleDisplayName() }}
                                            </span>
                                        </td>
                                        <td>{{ $doc->received_at ? $doc->received_at->format('M d, Y h:i A') : 'N/A' }}</td>
                                        <td>
                                            @if($doc->received_at)
                                                @php
                                                    $days = floor($doc->received_at->diffInDays(now()));
                                                    $badgeClass = $days > 30 ? 'danger' : ($days > 14 ? 'warning' : 'success');
                                                @endphp
                                                <span class="badge badge-{{ $badgeClass }}">{{ $days }} days</span>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            <span class="status-badge status-received">{{ $doc->status }}</span>
                                        </td>
                                        <td>
                                            <button class="btn btn-warning btn-sm transfer-from-my-docs-btn" 
                                                    data-doc-id="{{ $doc->id }}"
                                                    data-case-id="{{ $doc->case_id }}"
                                                    data-case-no="{{ $doc->case->case_no ?? 'N/A' }}"
                                                    title="Transfer">
                                                <i class="fas fa-exchange-alt"></i>
                                            </button>
                                            <button class="btn btn-info btn-sm view-history-btn" 
                                                    data-doc-id="{{ $doc->id }}"
                                                    title="View History">
                                                <i class="fas fa-history"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">
                                            <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                            No documents received yet.
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- All Documents Tab (Admin Only) -->
            @if(Auth::user()->isAdmin())
            <div class="tab-pane fade" id="allDocs" role="tabpanel">
                 <div class="filter-bar mb-4">
                <div class="row align-items-end">
                    <div class="col-md-4 mb-3 mb-md-0">
                        <label class="small font-weight-bold text-uppercase mb-1">Search Case / Establishment</label>
                        <input type="text" class="form-control" id="searchInput" placeholder="Type to filter...">
                    </div>
                    <div class="col-md-4 mb-3 mb-md-0">
                        <label class="small font-weight-bold text-uppercase mb-1">Filter by Department</label>
                        <select class="form-control" id="roleFilter">
                            <option value="">All Departments</option>
                            <option value="admin">Admin</option>
                            <option value="malsu">MALSU</option>
                            <option value="case_management">Case Management</option>
                            <option value="records">Records</option>
                            <option value="provinces">All Provinces</option> <!-- groups all province_* -->
                            <option value="province_albay">Albay Province</option>
                            <option value="province_camarines_sur">Camarines Sur Province</option>
                            <option value="province_camarines_norte">Camarines Norte Province</option>
                            <option value="province_catanduanes">Catanduanes Province</option>
                            <option value="province_masbate">Masbate Province</option>
                            <option value="province_sorsogon">Sorsogon Province</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-secondary btn-block mt-4" id="clearFilters">
                            <i class="fas fa-redo"></i> Clear Filters
                        </button>
                    </div>
                </div>
            </div>
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-primary">All Documents (Admin View)</h6>
                        <span class="badge badge-primary badge-pill">{{ $allDocuments->count() }} Total</span>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover tracking-table" id="allDocsTable">
                                <thead>
                                    <tr>
                                        <th>Case No.</th>
                                        <th>Establishment</th>
                                        <th>Current Department</th>
                                        <th>Status</th>
                                        <th>Transferred By</th>
                                        <th>Received By</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($allDocuments as $doc)
                                    <tr>
                                        <td class="font-weight-bold text-primary">{{ $doc->case->case_no ?? 'N/A' }}</td>
                                        <td>
                                            <div class="text-truncate" style="max-width: 180px;" title="{{ $doc->case->establishment_name ?? 'N/A' }}">
                                                {{ $doc->case->establishment_name ?? 'N/A' }}
                                            </div>
                                        </td>
                                        <td>
                                            <span class="role-badge role-{{ $doc->current_role }}">
                                                {{ $doc->getRoleDisplayName() }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="status-badge status-{{ strtolower(str_replace(' ', '', $doc->status)) }}">
                                                {{ $doc->status }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($doc->transferredBy)
                                                {{ $doc->transferredBy->fname }} {{ $doc->transferredBy->lname }}
                                            @else
                                                System
                                            @endif
                                        </td>
                                        <td>
                                            @if($doc->receivedBy)
                                                {{ $doc->receivedBy->fname }} {{ $doc->receivedBy->lname }}
                                            @else
                                                <span class="text-muted">Pending</span>
                                            @endif
                                        </td>
                                        <td>
                                            <button class="btn btn-info btn-sm view-history-btn" 
                                                    data-doc-id="{{ $doc->id }}"
                                                    title="View History">
                                                <i class="fas fa-history"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">
                                            <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                            No documents being tracked yet.
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            @endif

        </div>

    </div>
</div>

<!-- Transfer Document Modal -->
<div class="modal fade" id="transferModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-exchange-alt"></i> Transfer Document
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="transferForm">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Case <span class="text-danger">*</span></label>
                                <select class="form-control" name="case_id" id="transfer_case_id" required>
                                    <option value="">Select Case</option>
                                    @foreach($cases ?? [] as $case)
                                        <option value="{{ $case->id }}">
                                            {{ $case->case_no }} - {{ Str::limit($case->establishment_name, 30) }}
                                        </option>
                                    @endforeach
                                </select>
                                <small id="case-locked-msg" class="text-info" style="display:none;">
                                    <i class="fas fa-lock"></i> Case is locked for this transfer
                                </small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Transfer To Department <span class="text-danger">*</span></label>
                                <select class="form-control" name="target_role" id="target_role" required>
                                    <option value="">Select Department</option>
                                    <option value="admin">Admin</option>
                                    <option value="malsu">MALSU</option>
                                    <option value="case_management">Case Management</option>
                                    <option value="records">Records</option>
                                    <optgroup label="Province Offices">
                                        <option value="province_albay">Albay Province</option>
                                        <option value="province_camarines_sur">Camarines Sur Province</option>
                                        <option value="province_camarines_norte">Camarines Norte Province</option>
                                        <option value="province_catanduanes">Catanduanes Province</option>
                                        <option value="province_masbate">Masbate Province</option>
                                        <option value="province_sorsogon">Sorsogon Province</option>
                                    </optgroup>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold">Transfer Notes</label>
                        <textarea class="form-control" name="transfer_notes" id="transfer_notes" rows="3" placeholder="Optional notes about this transfer..."></textarea>
                    </div>

                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> The document will be marked as "Pending Receipt" until someone from the target department receives it.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i> Transfer Document
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- History Modal -->
<div class="modal fade" id="historyModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-history"></i> Document Transfer History
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <strong>Case:</strong> <span id="historyCaseNo"></span><br>
                    <strong>Establishment:</strong> <span id="historyEstablishment"></span>
                </div>
                <hr>
                <div class="timeline" id="historyTimeline">
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

<!-- Receive Confirmation Modal -->
<div class="modal fade" id="receiveModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="fas fa-check-circle"></i> Receive Document
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to receive this document?</p>
                <p class="mb-0"><strong>Case:</strong> <span id="receiveCaseNo"></span></p>
                <small class="text-muted">This will mark you as the receiver and move the document to your "My Documents" tab.</small>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="confirmReceiveBtn">
                    <i class="fas fa-check"></i> Confirm Receipt
                </button>
            </div>
        </div>
    </div>
</div>

@endsection
@push('scripts')
<script>
$(document).ready(function() {
    
    let docToReceive = null;
    let isTransferLocked = false;

    // Transfer form submission
    $('#transferForm').on('submit', function(e) {
        e.preventDefault();
        
        // Get form data manually
        const formData = {
            case_id: $('#transfer_case_id').val(),
            target_role: $('#target_role').val(),   
            transfer_notes: $('#transfer_notes').val(),
            _token: $('meta[name="csrf-token"]').attr('content')
        };

        console.log('Submitting:', formData);

        $.ajax({
            url: '/documents/transfer',
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
                console.error('Error:', xhr);
                let errorMsg = 'Failed to transfer document.';
                if (xhr.responseJSON?.message) {
                    errorMsg = xhr.responseJSON.message;
                } else if (xhr.responseJSON?.errors) {
                    const errors = Object.values(xhr.responseJSON.errors).flat();
                    errorMsg = errors.join(', ');
                }
                showAlert(errorMsg, 'danger');
            }
        });
    });

    // Transfer from My Documents
    $(document).on('click', '.transfer-from-my-docs-btn', function() {
        const caseId = $(this).data('case-id');
        const caseNo = $(this).data('case-no');
        
        console.log('Locking case:', caseId);
        
        isTransferLocked = true;
        
        // Set the value and make it readonly
        $('#transfer_case_id').val(caseId).prop('readonly', true)
            .css({
                'background-color': '#e9ecef',
                'cursor': 'not-allowed',
                'opacity': '0.6'
            });
        
        $('#case-locked-msg').show();
        $('#transferModal .modal-title').html('<i class="fas fa-exchange-alt"></i> Transfer Document - ' + caseNo);
        $('#transferModal').modal('show');
    });

    // Regular transfer button
    $('[data-target="#transferModal"]').on('click', function() {
        console.log('Opening fresh modal');
        isTransferLocked = false;
        $('#transfer_case_id').val('').prop('readonly', false)
            .css({
                'background-color': '',
                'cursor': '',
                'opacity': ''
            });
        $('#case-locked-msg').hide();
        $('#transferModal .modal-title').html('<i class="fas fa-exchange-alt"></i> Transfer Document');
    });

    // Receive button
    $(document).on('click', '.receive-btn', function() {
        docToReceive = $(this).data('doc-id');
        $('#receiveCaseNo').text($(this).data('case-no'));
        $('#receiveModal').modal('show');
    });

    // Confirm receive
    $('#confirmReceiveBtn').on('click', function() {
        if (!docToReceive) return;

        const button = $(this);
        button.html('<i class="fas fa-spinner fa-spin"></i> Receiving...').prop('disabled', true);

        $.ajax({
            url: '/documents/' + docToReceive + '/receive',
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                $('#receiveModal').modal('hide');
                showAlert(response.message || 'Document received successfully!', 'success');
                setTimeout(() => location.reload(), 1500);
            },
            error: function(xhr) {
                button.html('<i class="fas fa-check"></i> Confirm Receipt').prop('disabled', false);
                showAlert(xhr.responseJSON?.message || 'Failed to receive document.', 'danger');
                $('#receiveModal').modal('hide');
            }
        });
    });

    // View history
    $(document).on('click', '.view-history-btn', function() {
        const docId = $(this).data('doc-id');
        
        $('#historyModal').modal('show');
        $('#historyTimeline').html('<div class="text-center py-4"><div class="spinner-border text-primary"></div></div>');
        
        $.ajax({
            url: '/documents/' + docId + '/history',
            method: 'GET',
            success: function(response) {
                $('#historyCaseNo').text(response.case_no);
                $('#historyEstablishment').text(response.establishment);
                
                let html = '';
                response.history.forEach((item) => {
                    const roleClass = (item.role || '').toLowerCase().replace(/ /g, '_');
                    html += `
                        <div class="timeline-item">
                            <div class="card mb-0">
                                <div class="card-body py-3">
                                    <div class="d-flex justify-content-between mb-2">
                                        <div>
                                            <span class="role-badge role-${roleClass}">${item.role}</span>
                                            ${item.from_role ? '<small class="text-muted ml-2">from ' + item.from_role + '</small>' : ''}
                                        </div>
                                        <small class="text-muted">${item.time_ago}</small>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <small class="text-muted">Transferred By:</small><br>
                                            <strong>${item.transferred_by}</strong><br>
                                            <small class="text-muted">${item.transferred_at}</small>
                                        </div>
                                        <div class="col-md-6">
                                            <small class="text-muted">Received By:</small><br>
                                            <strong class="${item.received_by === 'Pending' ? 'text-warning' : 'text-success'}">
                                                ${item.received_by}
                                            </strong><br>
                                            <small class="text-muted">${item.received_at}</small>
                                        </div>
                                    </div>
                                    ${item.notes ? '<hr class="my-2"><small class="text-muted">Notes: ' + item.notes + '</small>' : ''}
                                </div>
                            </div>
                        </div>
                    `;
                });
                
                $('#historyTimeline').html(html || '<div class="alert alert-info">No history available</div>');
            },
            error: function() {
                $('#historyTimeline').html('<div class="alert alert-danger">Failed to load history</div>');
            }
        });
    });

    // Reset modals on close
    $('#transferModal').on('hidden.bs.modal', function() {
        $('#transferForm')[0].reset();
        $('#transfer_case_id').prop('readonly', false).css({'background-color': '', 'cursor': '', 'opacity': ''});
        $('#case-locked-msg').hide();
        isTransferLocked = false;
    });

    $('#receiveModal').on('hidden.bs.modal', function() {
        docToReceive = null;
        $('#confirmReceiveBtn').html('<i class="fas fa-check"></i> Confirm Receipt').prop('disabled', false);
    });
});

function showAlert(message, type) {
    const alertId = type === 'success' ? 'success-alert' : 'error-alert';
    const messageId = type === 'success' ? 'success-message' : 'error-message';
    
    $('#' + messageId).text(message);
    $('#' + alertId).removeClass('fade').addClass('show').show();
    
    setTimeout(() => $('#' + alertId).removeClass('show').addClass('fade'), 5000);
}

function hideAlert(alertId) {
    $('#' + alertId).removeClass('show').addClass('fade');
}

function filterTables() {
    const searchTerm = $('#searchInput').val().toLowerCase().trim();
    const role = $('#roleFilter').val();

    // Apply to ALL tables: pending, myDocs, allDocs
    $('.tracking-table tbody tr').each(function() {
        const $row = $(this);

        // Skip "no data" rows
        if ($row.find('td[colspan]').length > 0) return;

        // Get text content
        const caseNo = $row.find('td:eq(0)').text().toLowerCase();
        const establishment = $row.find('td:eq(1)').text().toLowerCase();
        const matchesSearch = !searchTerm || 
            caseNo.includes(searchTerm) || 
            establishment.includes(searchTerm);

        // Role filtering
        let rowRole = '';
        const $badge = $row.find('.role-badge');
        if ($badge.length) {
            const classes = $badge.attr('class').split(' ');
            classes.forEach(cls => {
                if (cls.startsWith('role-')) {
                    rowRole = cls.replace('role-', '');
                }
            });
        }

        let matchesRole = !role;
        if (role) {
            if (role === 'provinces') {
                matchesRole = rowRole.startsWith('province_');
            } else {
                matchesRole = rowRole === role;
            }
        }

        if (matchesSearch && matchesRole) {
            $row.show();
        } else {
            $row.hide();
        }
    });
}

// Bind events
$('#searchInput').on('keyup', filterTables);
$('#roleFilter').on('change', filterTables);

$('#clearFilters').on('click', function() {
    $('#searchInput').val('');
    $('#roleFilter').val('');
    filterTables();
});
</script>
@endpush