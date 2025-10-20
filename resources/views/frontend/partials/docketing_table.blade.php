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

<div class="d-flex justify-content-between align-items-center mb-3 custom-search-container">
    <div class="d-flex align-items-center">
        <label class="mr-2 mb-0" style="font-size: 0.8rem;">Search:</label>
        <input type="search" class="form-control form-control-sm" id="customSearch2" placeholder="Search docketing..." style="width: 200px;">
    </div>
</div>

<div class="table-container">
    <table class="table table-bordered compact-table sticky-table" id="dataTable2" width="100%" cellspacing="0">
        <thead>
            <tr>
                <th>Inspection ID</th>
                <th>Establishment Name</th>
                <th>Case No.</th>
                <th>PCT for Docketing</th>
                <th>Date Scheduled/Docketed</th>
                <th>Aging Docket</th>
                <th>Status Docket</th>
                <th>Hearing Officer (MIS)</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @if(isset($docketing) && $docketing->count() > 0)
                @foreach($docketing as $dock)
                    <tr data-id="{{ $dock->id }}">
                        <td class="editable-cell readonly-cell" data-field="inspection_id">{{ $dock->case->inspection_id ?? '-' }}</td>   
                        <td class="editable-cell readonly-cell" data-field="establishment_name" title="{{ $dock->case->establishment_name ?? '' }}">
                            {{ $dock->case ? Str::limit($dock->case->establishment_name, 25) : '-' }}
                        </td>
                        <td class="editable-cell" data-field="case_no" title="Click to edit Case No">{{ $dock->case->case_no ?? '-' }}</td>
                        <td class="editable-cell" data-field="pct_for_docketing">{{ $dock->pct_for_docketing ?? '-' }}</td>
                        <td class="editable-cell" data-field="date_scheduled_docketed" data-type="date">{{ $dock->date_scheduled_docketed ? \Carbon\Carbon::parse($dock->date_scheduled_docketed)->format('Y-m-d') : '-' }}</td>
                        <td class="editable-cell" data-field="aging_docket">{{ $dock->aging_docket ?? '-' }}</td>
                        <td class="editable-cell" data-field="status_docket" data-type="select">
                            @if($dock->status_docket)
                                <span class="badge badge-{{ $dock->status_docket == 'Completed' ? 'success' : ($dock->status_docket == 'In Progress' ? 'warning' : 'secondary') }}">
                                    {{ $dock->status_docket }}
                                </span>
                            @else
                                -
                            @endif
                        </td>
                        <td class="editable-cell" data-field="hearing_officer_mis">{{ $dock->hearing_officer_mis ?? '-' }}</td>
                        <td>
                            <button class="btn btn-warning btn-sm edit-row-btn-docketing" 
                                    data-docketing-id="{{ $dock->id }}"
                                    title="Edit Row">
                                <i class="fas fa-edit"></i>
                            </button>
                            
                            <button type="button" 
                                    class="btn btn-danger btn-sm delete-btn" 
                                    data-docketing-id="{{ $dock->id }}"
                                    data-establishment="{{ $dock->case->establishment_name ?? 'N/A' }}"
                                    title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                            
                            @if($dock->case && $dock->case->current_stage === '2: Docketing')
                                <button type="button" 
                                        class="btn btn-success btn-sm ml-1 move-to-next-stage-btn" 
                                        data-case-id="{{ $dock->case->id }}"
                                        data-case-no="{{ $dock->case->case_no ?? 'N/A' }}"
                                        data-establishment="{{ $dock->case->establishment_name ?? 'N/A' }}"
                                        data-stage="Docketing"
                                        title="Move to Hearing">
                                    <i class="fas fa-arrow-right"></i> Next
                                </button>
                            @endif
                        </td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="9" class="text-center">No docketing records found.</td>
                </tr>
            @endif
        </tbody>
    </table>
</div>