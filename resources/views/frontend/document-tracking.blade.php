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

        <!-- ============================================================ -->
        <!-- TAB NAV — ALL tab links live here, in the correct <ul>       -->
        <!-- ============================================================ -->
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

            @if(Auth::user()->role === 'case_management' || Auth::user()->isAdmin())
            <li class="nav-item">
                <a class="nav-link" id="malsu-pipeline-tab" data-toggle="tab" href="#malsuPipeline" role="tab">
                    <i class="fas fa-arrow-right-arrow-left"></i> Forwarded to MALSU
                    @if($forwardedToMalsu->where('current_role','malsu')->where('status','Pending Receipt')->count() > 0)
                        <span class="badge badge-danger ml-1">
                            {{ $forwardedToMalsu->where('current_role','malsu')->where('status','Pending Receipt')->count() }}
                        </span>
                    @endif
                </a>
            </li>
            @endif

            @if(Auth::user()->role === 'malsu' || Auth::user()->isAdmin())
            <li class="nav-item">
                <a class="nav-link" id="cm-pipeline-tab" data-toggle="tab" href="#cmPipeline" role="tab">
                    <i class="fas fa-arrow-right-arrow-left"></i> Forwarded to Case Management
                    @if($forwardedToCaseManagement->where('current_role','case_management')->where('status','Pending Receipt')->count() > 0)
                        <span class="badge badge-danger ml-1">
                            {{ $forwardedToCaseManagement->where('current_role','case_management')->where('status','Pending Receipt')->count() }}
                        </span>
                    @endif
                </a>
            </li>
            @endif

        </ul>
        <!-- END TAB NAV -->

        <!-- ============================================================ -->
        <!-- TAB CONTENT — ALL tab panes live here                        -->
        <!-- ============================================================ -->
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
            {{-- ↑ @endif closes the Admin-only allDocs pane HERE, before the new panes --}}

            {{-- ===== FORWARDED TO MALSU TAB (Case Management & Admin) ===== --}}
            @if(Auth::user()->role === 'case_management' || Auth::user()->isAdmin())
            <div class="tab-pane fade" id="malsuPipeline" role="tabpanel">
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-gavel"></i> Forwarded to MALSU
                        </h6>
                        <span class="badge badge-primary badge-pill">
                            {{ $forwardedToMalsu->count() }} case(s)
                        </span>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover tracking-table" id="malsuPipelineTable">
                                <thead>
                                    <tr>
                                        <th>Case No.</th>
                                        <th>Establishment</th>
                                        <th>Current Location</th>
                                        <th>Status</th>
                                        <th>Transferred By</th>
                                        <th>Transferred At</th>
                                        <th>Received By</th>
                                        <th>Notes</th>
                                        <th>Transfer History</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($forwardedToMalsu as $doc)
                                    @php
                                        $isReturned = $doc->transfer_notes && 
                                                    str_contains($doc->transfer_notes, '[RETURNED]');
                                        $rowClass   = $isReturned ? 'table-danger' : '';
                                        
                                        $senderRole  = optional($doc->transferredBy)->role;
                                        $showReturn  = $doc->current_role === Auth::user()->role
                                                    && $doc->status === 'Received'
                                                    && $senderRole === 'malsu';
                                    @endphp
                                    <tr class="{{ $rowClass }}">
                                        <td class="font-weight-bold text-primary">
                                            {{ $doc->case->case_no ?? 'N/A' }}
                                        </td>
                                        <td>
                                            <div class="text-truncate" style="max-width: 180px;" 
                                                title="{{ $doc->case->establishment_name ?? '' }}">
                                                {{ $doc->case->establishment_name ?? 'N/A' }}
                                            </div>
                                        </td>
                                        <td>
                                            <span class="role-badge role-{{ $doc->current_role }}">
                                                {{ $doc->getRoleDisplayName() }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($isReturned)
                                                <span class="status-badge" 
                                                    style="background:#e74a3b;color:white;">
                                                    Returned
                                                </span>
                                            @elseif($doc->status === 'Received')
                                                <span class="status-badge status-received">
                                                    Received
                                                </span>
                                            @else
                                                <span class="status-badge status-pending">
                                                    Pending Receipt
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            {{ optional($doc->transferredBy)->fname }} 
                                            {{ optional($doc->transferredBy)->lname ?? 'System' }}
                                        </td>
                                        <td>
                                            {{ $doc->transferred_at 
                                                ? $doc->transferred_at->format('M d, Y h:i A') 
                                                : 'N/A' }}
                                        </td>
                                        <td>
                                            {{ optional($doc->receivedBy)->fname }} 
                                            {{ optional($doc->receivedBy)->lname ?? '—' }}
                                        </td>
                                        <td>
                                            @if($doc->transfer_notes)
                                                <span class="{{ $isReturned ? 'text-danger font-weight-bold' : 'text-muted' }}"
                                                    style="font-size:0.8rem;">
                                                    {{ $doc->transfer_notes }}
                                                </span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            @php
                                                $relevantHistory = $doc->history->filter(function($h) {
                                                    return (
                                                        ($h->from_role === 'case_management' && $h->to_role === 'malsu') ||
                                                        ($h->from_role === 'malsu' && $h->to_role === 'case_management') ||
                                                        ($h->from_role === 'case_management' && $h->to_role === 'case_management') ||
                                                        ($h->from_role === 'malsu' && $h->to_role === 'malsu')
                                                    );
                                                });
                                            @endphp
                                            @if($relevantHistory->count() > 0)
                                                <button class="btn btn-info btn-sm view-pipeline-history-btn"
                                                        data-doc-id="{{ $doc->id }}"
                                                        data-case-no="{{ $doc->case->case_no ?? 'N/A' }}"
                                                        data-establishment="{{ $doc->case->establishment_name ?? 'N/A' }}"
                                                        title="View Transfer History">
                                                    <i class="fas fa-history"></i>
                                                    {{ $relevantHistory->count() }} transfer(s)
                                                </button>
                                            @else
                                                <span class="text-muted small">No history yet</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($showReturn)
                                                <button class="btn btn-danger btn-sm return-doc-btn"
                                                        data-doc-id="{{ $doc->id }}"
                                                        data-case-no="{{ $doc->case->case_no ?? 'N/A' }}"
                                                        data-target-role="malsu"
                                                        title="Return to MALSU">
                                                    <i class="fas fa-undo"></i> Return
                                                </button>
                                            @else
                                                <span class="text-muted small">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="10" class="text-center text-muted py-4">
                                            <i class="fas fa-gavel fa-2x mb-2 d-block"></i>
                                            No cases forwarded to MALSU yet.
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

            {{-- ===== FORWARDED TO CASE MANAGEMENT TAB (MALSU & Admin) ===== --}}
            @if(Auth::user()->role === 'malsu' || Auth::user()->isAdmin())
            <div class="tab-pane fade" id="cmPipeline" role="tabpanel">
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-briefcase"></i> Forwarded to Case Management
                        </h6>
                        <span class="badge badge-primary badge-pill">
                            {{ $forwardedToCaseManagement->count() }} case(s)
                        </span>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover tracking-table" id="cmPipelineTable">
                                <thead>
                                    <tr>
                                        <th>Case No.</th>
                                        <th>Establishment</th>
                                        <th>Current Location</th>
                                        <th>Status</th>
                                        <th>Transferred By</th>
                                        <th>Transferred At</th>
                                        <th>Received By</th>
                                        <th>Notes</th>
                                        <th>Transfer History</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($forwardedToCaseManagement as $doc)
                                    @php
                                        $isReturned = $doc->transfer_notes && 
                                                    str_contains($doc->transfer_notes, '[RETURNED]');
                                        $rowClass   = $isReturned ? 'table-danger' : '';

                                        $senderRole = optional($doc->transferredBy)->role;
                                        $showReturn = $doc->current_role === Auth::user()->role
                                                && $doc->status === 'Received'
                                                && $senderRole === 'case_management';
                                    @endphp
                                    <tr class="{{ $rowClass }}">
                                        <td class="font-weight-bold text-primary">
                                            {{ $doc->case->case_no ?? 'N/A' }}
                                        </td>
                                        <td>
                                            <div class="text-truncate" style="max-width: 180px;"
                                                title="{{ $doc->case->establishment_name ?? '' }}">
                                                {{ $doc->case->establishment_name ?? 'N/A' }}
                                            </div>
                                        </td>
                                        <td>
                                            <span class="role-badge role-{{ $doc->current_role }}">
                                                {{ $doc->getRoleDisplayName() }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($isReturned)
                                                <span class="status-badge" 
                                                    style="background:#e74a3b;color:white;">
                                                    Returned
                                                </span>
                                            @elseif($doc->status === 'Received')
                                                <span class="status-badge status-received">
                                                    Received
                                                </span>
                                            @else
                                                <span class="status-badge status-pending">
                                                    Pending Receipt
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            {{ optional($doc->transferredBy)->fname }}
                                            {{ optional($doc->transferredBy)->lname ?? 'System' }}
                                        </td>
                                        <td>
                                            {{ $doc->transferred_at 
                                                ? $doc->transferred_at->format('M d, Y h:i A') 
                                                : 'N/A' }}
                                        </td>
                                        <td>
                                            {{ optional($doc->receivedBy)->fname }}
                                            {{ optional($doc->receivedBy)->lname ?? '—' }}
                                        </td>
                                        <td>
                                            @if($doc->transfer_notes)
                                                <span class="{{ $isReturned ? 'text-danger font-weight-bold' : 'text-muted' }}"
                                                    style="font-size:0.8rem;">
                                                    {{ $doc->transfer_notes }}
                                                </span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            @php
                                                $relevantHistory = $doc->history->filter(function($h) {
                                                    return (
                                                        ($h->from_role === 'malsu' && $h->to_role === 'case_management') ||
                                                        ($h->from_role === 'case_management' && $h->to_role === 'malsu') ||
                                                        ($h->from_role === 'malsu' && $h->to_role === 'malsu') ||
                                                        ($h->from_role === 'case_management' && $h->to_role === 'case_management')
                                                    );
                                                });
                                            @endphp
                                            @if($relevantHistory->count() > 0)
                                                <button class="btn btn-info btn-sm view-pipeline-history-btn"
                                                        data-doc-id="{{ $doc->id }}"
                                                        data-case-no="{{ $doc->case->case_no ?? 'N/A' }}"
                                                        data-establishment="{{ $doc->case->establishment_name ?? 'N/A' }}"
                                                        title="View Transfer History">
                                                    <i class="fas fa-history"></i>
                                                    {{ $relevantHistory->count() }} transfer(s)
                                                </button>
                                            @else
                                                <span class="text-muted small">No history yet</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($showReturn)
                                                <button class="btn btn-danger btn-sm return-doc-btn"
                                                        data-doc-id="{{ $doc->id }}"
                                                        data-case-no="{{ $doc->case->case_no ?? 'N/A' }}"
                                                        data-target-role="case_management"
                                                        title="Return to Case Management">
                                                    <i class="fas fa-undo"></i> Return
                                                </button>
                                            @else
                                                <span class="text-muted small">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="10" class="text-center text-muted py-4">
                                            <i class="fas fa-briefcase fa-2x mb-2 d-block"></i>
                                            No cases forwarded to Case Management yet.
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
        <!-- END TAB CONTENT -->

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

<!-- Return Document Modal -->
<div class="modal fade" id="returnDocModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="fas fa-undo"></i> Return Document
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to <strong>return</strong> this document?</p>
                <p class="mb-1">
                    <strong>Case:</strong> 
                    <span id="returnCaseNo" class="text-primary"></span>
                </p>
                <small class="text-muted">
                    The document will be sent back and marked as 
                    <span class="text-danger font-weight-bold">Returned</span>.
                </small>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    Cancel
                </button>
                <button type="button" class="btn btn-danger" id="confirmReturnBtn">
                    <i class="fas fa-undo"></i> Confirm Return
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Pipeline History Modal -->
<div class="modal fade" id="pipelineHistoryModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-history"></i> MALSU ↔ Case Management Transfer History
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <strong>Case:</strong> <span id="pipelineCaseNo"></span><br>
                    <strong>Establishment:</strong> <span id="pipelineEstablishment"></span>
                </div>
                <hr>
                <div class="timeline" id="pipelineHistoryTimeline">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status"></div>
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
@push('scripts')
<script>
$(document).ready(function() {
    
    let docToReceive = null;
    let isTransferLocked = false;

    // Transfer form submission
    $('#transferForm').on('submit', function(e) {
        e.preventDefault();
        
        const formData = {
            case_id: $('#transfer_case_id').val(),
            target_role: $('#target_role').val(),   
            transfer_notes: $('#transfer_notes').val(),
            _token: $('meta[name="csrf-token"]').attr('content')
        };

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
        
        isTransferLocked = true;
        
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

                if (!response.history || !response.history.length) {
                    $('#historyTimeline').html('<div class="alert alert-info">No history available.</div>');
                    return;
                }

                let html = '';

                response.history.forEach((item, index) => {
                    const isReturned  = (item.notes || '').includes('[RETURNED]');
                    const isCurrent   = item.is_current;
                    const isPending   = item.received_by === 'Awaiting Receipt' || item.received_by === 'Pending';

                    // Status badge colour
                    let badgeColor = 'primary';
                    let statusLabel = item.status;
                    if (isReturned)        { badgeColor = 'danger';  statusLabel = 'Returned'; }
                    else if (isCurrent && item.status === 'Received') { badgeColor = 'success'; }
                    else if (isCurrent && isPending)                  { badgeColor = 'warning'; statusLabel = 'Pending Receipt'; }
                    else if (!isCurrent)   { badgeColor = 'secondary'; }

                    // Direction arrow: "From → To" or just "Current Location"
                    let directionHtml = '';
                    if (item.from_role_name && item.role) {
                        directionHtml = `
                            <span class="text-muted small">
                                <strong>${item.from_role_name}</strong>
                                <i class="fas fa-arrow-right mx-1"></i>
                                <strong>${item.role}</strong>
                            </span>`;
                    } else {
                        directionHtml = `<span class="text-muted small"><strong>${item.role}</strong></span>`;
                    }

                    // Card border highlight
                    const cardClass = isReturned ? 'border-danger' : (isCurrent ? 'border-primary' : '');

                    // Receiver display
                    const receiverClass = isPending ? 'text-warning' : 'text-success';

                    // Notes — strip [RETURNED] prefix for display
                    const displayNotes = (item.notes || '').replace('[RETURNED]', '').trim();

                    html += `
                        <div class="timeline-item">
                            <div class="card mb-0 ${cardClass}">
                                <div class="card-body py-3">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <span class="badge badge-${badgeColor}">${statusLabel}</span>
                                            <div class="mt-1">${directionHtml}</div>
                                        </div>
                                        <small class="text-muted text-right">${item.time_ago}</small>
                                    </div>
                                    <div class="row mt-2">
                                        <div class="col-md-6">
                                            <small class="text-muted">Transferred By:</small><br>
                                            <strong>${item.transferred_by}</strong><br>
                                            <small class="text-muted">${item.transferred_at}</small>
                                        </div>
                                        <div class="col-md-6">
                                            <small class="text-muted">Received By:</small><br>
                                            <strong class="${receiverClass}">${item.received_by}</strong><br>
                                            <small class="text-muted">${item.received_at}</small>
                                        </div>
                                    </div>
                                    ${displayNotes ? `<hr class="my-2"><small class="${isReturned ? 'text-danger font-weight-bold' : 'text-muted'}"><i class="fas fa-sticky-note mr-1"></i>${displayNotes}</small>` : ''}
                                </div>
                            </div>
                        </div>
                    `;
                });

                $('#historyTimeline').html(html);
            },
            error: function() {
                $('#historyTimeline').html('<div class="alert alert-danger">Failed to load history.</div>');
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

// ── Return document ──────────────────────────────────────────
let docToReturn = null;

$(document).on('click', '.return-doc-btn', function() {
    docToReturn = $(this).data('doc-id');
    $('#returnCaseNo').text($(this).data('case-no'));
    $('#returnDocModal').modal('show');
});

$('#confirmReturnBtn').on('click', function() {
    if (!docToReturn) return;

    const btn = $(this);
    btn.html('<i class="fas fa-spinner fa-spin"></i> Returning...').prop('disabled', true);

    $.ajax({
        url: '/documents/' + docToReturn + '/return',
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        success: function(response) {
            $('#returnDocModal').modal('hide');
            showAlert(response.message, 'success');
            setTimeout(() => location.reload(), 1500);
        },
        error: function(xhr) {
            btn.html('<i class="fas fa-undo"></i> Confirm Return').prop('disabled', false);
            showAlert(
                xhr.responseJSON?.message || 'Failed to return document.',
                'danger'
            );
            $('#returnDocModal').modal('hide');
        }
    });
});

$('#returnDocModal').on('hidden.bs.modal', function() {
    docToReturn = null;
    $('#confirmReturnBtn')
        .html('<i class="fas fa-undo"></i> Confirm Return')
        .prop('disabled', false);
});

// ── Pipeline history modal ────────────────────────────────────
$(document).on('click', '.view-pipeline-history-btn', function() {
    const docId         = $(this).data('doc-id');
    const caseNo        = $(this).data('case-no');
    const establishment = $(this).data('establishment');

    $('#pipelineCaseNo').text(caseNo);
    $('#pipelineEstablishment').text(establishment);
    $('#pipelineHistoryTimeline').html(
        '<div class="text-center py-4"><div class="spinner-border text-primary"></div></div>'
    );
    $('#pipelineHistoryModal').modal('show');

    $.ajax({
        url: '/documents/' + docId + '/history',
        method: 'GET',
        success: function(response) {
            const relevant = response.history.filter(item =>
                (item.from_role === 'case_management' || item.from_role === 'malsu' ||
                 item.to_role   === 'case_management' || item.to_role   === 'malsu')
            );

            if (!relevant.length) {
                $('#pipelineHistoryTimeline').html(
                    '<div class="alert alert-info">No relevant transfer history.</div>'
                );
                return;
            }

            let html = '';
            relevant.forEach(item => {
                const isReturned = (item.notes || '').includes('[RETURNED]');
                const cardBorder = isReturned ? 'border-danger' : '';
                const badgeColor = isReturned ? 'danger'
                                 : item.status === 'Received' ? 'success' : 'warning';
                const statusText = isReturned ? 'Returned'
                                 : (item.status || 'Pending');

                html += `
                <div class="timeline-item">
                    <div class="card mb-0 ${cardBorder}">
                        <div class="card-body py-3">
                            <div class="d-flex justify-content-between mb-2">
                                <div>
                                    <span class="badge badge-${badgeColor}">${statusText}</span>
                                    ${item.from_role
                                        ? `<small class="text-muted ml-2">
                                               from <strong>${item.from_role.replace('_',' ')}</strong>
                                               → <strong>${(item.role || item.to_role || '').replace('_',' ')}</strong>
                                           </small>`
                                        : ''}
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
                                    <strong class="${item.received_by === 'Pending' || item.received_by === 'Awaiting Receipt' ? 'text-warning' : 'text-success'}">
                                        ${item.received_by}
                                    </strong><br>
                                    <small class="text-muted">${item.received_at}</small>
                                </div>
                            </div>
                            ${item.notes
                                ? `<hr class="my-2">
                                   <small class="${isReturned ? 'text-danger font-weight-bold' : 'text-muted'}">
                                       <i class="fas fa-sticky-note"></i> ${item.notes}
                                   </small>`
                                : ''}
                        </div>
                    </div>
                </div>`;
            });

            $('#pipelineHistoryTimeline').html(html);
        },
        error: function() {
            $('#pipelineHistoryTimeline').html(
                '<div class="alert alert-danger">Failed to load history.</div>'
            );
        }
    });
});
</script>
@endpush