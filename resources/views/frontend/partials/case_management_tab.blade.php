{{-- 
    Partial: case_management_tab.blade.php
    Shows cases currently located at the case_management role.
    Mirrors Tab 0 (All Active Cases) layout but scoped to this role's queue.
--}}

<div class="alert alert-success alert-dismissible fade" role="alert" id="success-alert-tabCM" style="display: none;">
    <span id="success-message-tabCM"></span>
    <button type="button" class="close" onclick="hideAlert('success-alert-tabCM')">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
<div class="alert alert-danger alert-dismissible fade" role="alert" id="error-alert-tabCM" style="display: none;">
    <span id="error-message-tabCM"></span>
    <button type="button" class="close" onclick="hideAlert('error-alert-tabCM')">
        <span aria-hidden="true">&times;</span>
    </button>
</div>

<div class="d-flex justify-content-between align-items-center mb-3 custom-search-container">
    <div class="d-flex align-items-center">
        <label class="mr-2 mb-0" style="font-size: 0.8rem;">Search:</label>
        <input type="search"
               class="form-control form-control-sm"
               id="customSearchCM"
               placeholder="Search cases assigned to Case Management..."
               style="width: 260px;">
    </div>
    <div>
        <span class="badge badge-info" style="font-size: 0.85rem; padding: 0.45rem 0.8rem;">
            <i class="fas fa-folder-open mr-1"></i>
            {{ $cases->count() }} case(s) currently with Case Management
        </span>
    </div>
</div>

    <table class="table table-bordered compact-table sticky-table cm-table"
        id="dataTableCM"
        width="100%"
        cellspacing="0">
        <thead>
            <tr>
                <th>Actions</th>
                <th>No.</th>
                <th>Inspection ID</th>
                <th style="background-color: #fff3cd !important;">Case No.</th>
                <th style="background-color: #d1ecf1 !important;">Establishment Name</th>
                <th>Mode</th>
                <th style="background-color: #d4edda !important;">PO</th>
                <th>Type of Industry</th>
                <th>Date of Inspection</th>
                <th>Name of Inspector</th>
                <th>Authority No.</th>
                <th>Date of NR</th>
                <th>Hearing Officer (MIS)</th>
                <th>PCT (96 days from NR)</th>
                <th>Status (PO PCT)</th>
                <th>Date Signed (MIS)</th>
                <th>Created At</th>
            </tr>
        </thead>
        <tbody>
            @if($cases->count() > 0)
                @foreach($cases as $case)
                    <tr data-id="{{ $case->id }}">
                        {{-- Actions cell mirrors Tab 0 --}}
                        <td class="actions-cell collapsed">
                            <div class="action-buttons-container">
                                <button class="action-toggle-btn" type="button">
                                    <i class="fas fa-chevron-right"></i>
                                </button>
                                <div class="action-buttons">
                                    <button class="btn btn-warning btn-sm edit-row-btn-case"
                                            data-case-id="{{ $case->id }}"
                                            title="Edit Row">
                                        <i class="fas fa-edit"></i>
                                    </button>

                                    <button type="button"
                                            class="btn btn-danger btn-sm delete-btn"
                                            data-case-id="{{ $case->id }}"
                                            data-case-no="{{ $case->case_no ?? 'N/A' }}"
                                            data-establishment="{{ $case->establishment_name ?? 'N/A' }}"
                                            title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>

                                    <button class="btn btn-info btn-sm view-history-btn"
                                            data-case-id="{{ $case->id }}"
                                            data-case-no="{{ $case->case_no ?? 'N/A' }}"
                                            data-establishment="{{ $case->establishment_name ?? 'N/A' }}"
                                            title="View Document History">
                                        <i class="fas fa-history"></i>
                                    </button>

                                    <button type="button"
                                            class="btn btn-primary btn-sm document-checklist-btn"
                                            data-case-id="{{ $case->id }}"
                                            data-case-no="{{ $case->case_no ?? 'N/A' }}"
                                            data-establishment="{{ $case->establishment_name ?? 'N/A' }}"
                                            title="Document Checklist">
                                        <i class="fas fa-file-alt"></i>
                                    </button>

                                    {{-- Case management users complete cases --}}
                                    <button type="button"
                                            class="btn btn-success btn-sm complete-case-btn"
                                            data-case-id="{{ $case->id }}"
                                            data-case-no="{{ $case->case_no ?? 'N/A' }}"
                                            data-establishment="{{ $case->establishment_name ?? 'N/A' }}"
                                            data-stage="{{ explode(': ', $case->current_stage)[1] ?? $case->current_stage ?? 'Unknown' }}"
                                            title="Mark as Complete">
                                        <i class="fas fa-check-circle"></i>
                                    </button>
                                </div>
                            </div>
                        </td>

                        <td class="editable-cell" data-field="no">{{ $case->no ?? '-' }}</td>
                        <td class="editable-cell" data-field="inspection_id">{{ $case->inspection_id ?? '-' }}</td>
                        <td class="editable-cell" data-field="case_no" style="background-color: #fff3cd !important;">{{ $case->case_no ?? '-' }}</td>
                        <td class="editable-cell wrap-cell" data-field="establishment_name" style="background-color: #d1ecf1 !important;">{{ $case->establishment_name ?? '-' }}</td>
                        <td class="editable-cell" data-field="mode">{{ $case->mode ?? '-' }}</td>
                        <td class="readonly-cell" data-field="po_office" style="background-color: #d4edda !important;">{{ $case->po_office ?? '-' }}</td>
                        <td class="editable-cell" data-field="type_of_industry">{{ $case->type_of_industry ?? '-' }}</td>
                        <td class="editable-cell" data-field="date_of_inspection" data-type="date">
                            {{ $case->date_of_inspection ? \Carbon\Carbon::parse($case->date_of_inspection)->format('Y-m-d') : '-' }}
                        </td>
                        <td class="editable-cell" data-field="inspector_name" title="{{ $case->inspector_name ?? '' }}">
                            {{ $case->inspector_name ? Str::limit($case->inspector_name, 20) : '-' }}
                        </td>
                        <td class="editable-cell" data-field="inspector_authority_no">{{ $case->inspector_authority_no ?? '-' }}</td>
                        <td class="editable-cell" data-field="date_of_nr" data-type="date">
                            {{ $case->date_of_nr ? \Carbon\Carbon::parse($case->date_of_nr)->format('Y-m-d') : '-' }}
                        </td>
                        <td class="editable-cell" data-field="hearing_officer_mis" title="{{ $case->hearing_officer_mis ?? '' }}">
                            {{ $case->hearing_officer_mis ? Str::limit($case->hearing_officer_mis, 20) : '-' }}
                        </td>
                        <td class="editable-cell" data-field="pct_96_days">
                            {{ $case->pct_96_days ? $case->pct_96_days->format('Y-m-d') : '-' }}
                        </td>
                        <td class="editable-cell" data-field="status_pct">{{ $case->status_pct ?? '-' }}</td>
                        <td class="editable-cell" data-field="date_signed_mis" data-type="date">
                            {{ $case->date_signed_mis ? \Carbon\Carbon::parse($case->date_signed_mis)->format('Y-m-d') : '-' }}
                        </td>
                        <td class="non-editable">
                            {{ $case->created_at ? \Carbon\Carbon::parse($case->created_at)->format('Y-m-d') : '-' }}
                        </td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="17" class="text-center text-muted py-4">
                        <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                        No cases are currently assigned to Case Management.
                    </td>
                </tr>
            @endif
        </tbody>
    </table>