<div class="alert alert-success alert-dismissible fade" role="alert" id="success-alert-tab3" style="display: none;">
    <span id="success-message-tab3"></span>
    <button type="button" class="close" onclick="hideAlert('success-alert-tab3')">
        <span aria-hidden="true">&times;</span>
    </button>
</div>

<div class="alert alert-danger alert-dismissible fade" role="alert" id="error-alert-tab3" style="display: none;">
    <span id="error-message-tab3"></span>
    <button type="button" class="close" onclick="hideAlert('error-alert-tab3')">
        <span aria-hidden="true">&times;</span>
    </button>
</div>

<div class="d-flex justify-content-between align-items-center mb-3 custom-search-container">
    <div class="d-flex align-items-center">
        <label class="mr-2 mb-0" style="font-size: 0.8rem;">Search:</label>
        <input type="search" class="form-control form-control-sm" id="customSearch3" placeholder="Search hearing..." style="width: 200px;">
    </div>
</div>

<div class="table-container">
    <table class="table table-bordered compact-table sticky-table" id="dataTable3" width="100%" cellspacing="0">
        <thead>
            <tr>
                <th>Inspection ID</th>
                <th>Establishment Name</th>
                <th>Date 1st MC (Actual)</th>
                <th>1st MC PCT</th>
                <th>Status 1st MC</th>
                <th>Date 2nd/Last MC</th>
                <th>2nd/Last MC PCT</th>
                <th>Status 2nd MC</th>
                <th>Case Folder Forwarded to RO</th>
                <th>Complete Case Folder</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @if(isset($hearingProcess) && $hearingProcess->count() > 0)
                @foreach($hearingProcess as $hearing)
                    <tr data-id="{{ $hearing->id }}">
                        <td class="editable-cell readonly-cell" data-field="inspection_id">{{ $hearing->case->inspection_id ?? '-' }}</td>
                        <td class="editable-cell readonly-cell" data-field="establishment_name" title="{{ $hearing->case->establishment_name ?? '' }}">
                            {{ $hearing->case ? Str::limit($hearing->case->establishment_name, 25) : '-' }}
                        </td>
                        <td class="editable-cell" data-field="date_1st_mc_actual" data-type="date">{{ $hearing->date_1st_mc_actual ? \Carbon\Carbon::parse($hearing->date_1st_mc_actual)->format('Y-m-d') : '-' }}</td>
                        <td class="editable-cell" data-field="first_mc_pct">{{ $hearing->first_mc_pct ?? '-' }}</td>
                        <td class="editable-cell" data-field="status_1st_mc" data-type="select">
                            @if($hearing->status_1st_mc)
                                <span class="badge badge-{{ $hearing->status_1st_mc == 'Completed' ? 'success' : 'warning' }}">{{ $hearing->status_1st_mc }}</span>
                            @else
                                -
                            @endif
                        </td>
                        <td class="editable-cell" data-field="date_2nd_last_mc" data-type="date">{{ $hearing->date_2nd_last_mc ? \Carbon\Carbon::parse($hearing->date_2nd_last_mc)->format('Y-m-d') : '-' }}</td>
                        <td class="editable-cell" data-field="second_last_mc_pct">{{ $hearing->second_last_mc_pct ?? '-' }}</td>
                        <td class="editable-cell" data-field="status_2nd_mc" data-type="select">
                            @if($hearing->status_2nd_mc)
                                <span class="badge badge-{{ $hearing->status_2nd_mc == 'Completed' ? 'success' : 'warning' }}">{{ $hearing->status_2nd_mc }}</span>
                            @else
                                -
                            @endif
                        </td>
                        <td class="editable-cell" data-field="case_folder_forwarded_to_ro">{{ $hearing->case_folder_forwarded_to_ro ?? '-' }}</td>
                        <td class="editable-cell" data-field="complete_case_folder" data-type="select">
                            @if($hearing->complete_case_folder)
                                <span class="badge badge-{{ $hearing->complete_case_folder == 'Y' ? 'success' : 'warning' }}">{{ $hearing->complete_case_folder }}</span>
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            <button class="btn btn-warning btn-sm edit-row-btn-hearing" 
                                    data-hearing-id="{{ $hearing->id }}"
                                    title="Edit Row">
                                <i class="fas fa-edit"></i>
                            </button>
                            
                            <button type="button" 
                                    class="btn btn-danger btn-sm delete-btn" 
                                    data-hearing-id="{{ $hearing->id }}"
                                    data-establishment="{{ $hearing->case->establishment_name ?? 'N/A' }}"
                                    title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                            
                            @if($hearing->case && $hearing->case->current_stage === '3: Hearing')
                                <button type="button" 
                                        class="btn btn-success btn-sm ml-1 move-to-next-stage-btn" 
                                        data-case-id="{{ $hearing->case->id }}"
                                        data-case-no="{{ $hearing->case->case_no ?? 'N/A' }}"
                                        data-establishment="{{ $hearing->case->establishment_name ?? 'N/A' }}"
                                        data-stage="Hearing Process"
                                        title="Move to Review & Drafting">
                                    <i class="fas fa-arrow-right"></i> Next
                                </button>
                            @endif
                        </td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="11" class="text-center">No hearing records found.</td>
                </tr>
            @endif
        </tbody>
    </table>
</div>