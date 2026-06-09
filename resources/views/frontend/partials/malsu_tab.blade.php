{{-- 
    Partial: case_management_tab.blade.php
    Shows cases currently located at the case_management role.
--}}

<div class="alert alert-success alert-dismissible fade" role="alert" id="success-alert-tabMALSU" style="display: none;">
    <span id="success-message-tabMALSU"></span>
    <button type="button" class="close" onclick="hideAlert(' success-alert-tabMALSU')">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
<div class="alert alert-danger alert-dismissible fade" role="alert" id="error-alert-tabMALSU" style="display: none;">
    <span id="error-message-tabMALSU"></span>
    <button type="button" class="close" onclick="hideAlert('error-alert-tabMALSU')">
        <span aria-hidden="true">&times;</span>
    </button>
</div>

<div class="d-flex justify-content-between align-items-center mb-3 custom-search-container">
    <div class="d-flex align-items-center">
        <label class="mr-2 mb-0" style="font-size: 0.8rem;">Search:</label>
        <input type="search"
               class="form-control form-control-sm"
               id="customSearchMALSU"
               placeholder="Search cases assigned to MALSU..."
               style="width: 260px;">
    </div>
    <div>
        <span class="badge badge-info" style="font-size: 0.85rem; padding: 0.45rem 0.8rem;">
            <i class="fas fa-folder-open mr-1"></i>
            {{ $cases->count() }} case(s) currently with MALSU
        </span>
    </div>
</div>

<div class="table-container">
    <table class="table table-bordered compact-table sticky-table cm-table"
           id="dataTableMALSU"
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
                <th>Lapse 20 Day Correction Period</th>
                <th>PCT for Docketing</th>
                <th>Date Scheduled/Docketed</th>
                <th>Aging (Docket)</th>
                <th>Status (Docket)</th>
                <th>Hearing Officer (MIS)</th>
                <th>Date of 1st MC (Actual)</th>
                <th>1st MC PCT</th>
                <th>Status (1st MC)</th>
                <th>Date of 2nd/Last MC (Actual)</th>
                <th>2nd/Last MC PCT</th>
                <th>Status (2nd MC)</th>
                <th>Case Folder Forwarded to RO</th>
                <th>PO PCT</th>
                <th>Aging (PO PCT)</th>
                <th>Status (PO PCT)</th>
                <th>PCT (96 days from NR)</th>
                <th>Status (PCT)</th>
                <th>Date Signed (MIS)</th>
                <th>Created At</th>
            </tr>
        </thead>
        <tbody>
            @if($cases->count() > 0)
                @foreach($cases as $case)
                    <tr data-id="{{ $case->id }}">
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
                        <td class="editable-cell wrap-cell" data-field="establishment_name" 
                            data-address="{{ $case->establishment_address ?? '' }}"
                            style="background-color: #d1ecf1 !important;">
                            <span>{{ $case->establishment_name ?? '-' }}</span>
                            @if($case->establishment_address)
                                <br><small class="text-muted address-subtext" 
                                        style="font-weight: normal; font-size: 0.75rem;">
                                    {{ $case->establishment_address }}
                                </small>
                            @endif
                            @if($case->documentTracking && $case->documentTracking->case_tag)
                                <br>
                                @php
                                    $tagColors = [
                                        'For Execution'              => 'danger',
                                        'Motion for Reconsideration' => 'warning',
                                    ];
                                    $tagColor = $tagColors[$case->documentTracking->case_tag] ?? 'secondary';
                                @endphp
                                <span class="badge badge-{{ $tagColor }} mt-1" style="font-size: 0.7rem;">
                                    <i class="fas fa-bolt mr-1"></i>
                                    {{ strtoupper($case->documentTracking->case_tag) }}
                                </span>
                            @endif
                        </td>
                        <td class="editable-cell" data-field="mode">{{ $case->mode ?? '-' }}</td>
                        <td class="readonly-cell" data-field="po_office" style="background-color: #d4edda !important;">{{ $case->po_office ?? '-' }}</td>
                        <td class="editable-cell" data-field="type_of_industry">{{ $case->type_of_industry ?? '-' }}</td>

                        {{-- Inspection Stage --}}
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
                        <td class="readonly-cell" data-field="lapse_20_day_period">
                            {{ $case->lapse_20_day_period ? \Carbon\Carbon::parse($case->lapse_20_day_period)->format('Y-m-d') : '-' }}
                        </td>

                        {{-- Docketing Stage --}}
                        <td class="readonly-cell" data-field="pct_for_docketing">
                            {{ $case->pct_for_docketing ? \Carbon\Carbon::parse($case->pct_for_docketing)->format('Y-m-d') : '-' }}
                        </td>
                        <td class="editable-cell" data-field="date_scheduled_docketed" data-type="date">
                            {{ $case->date_scheduled_docketed ? \Carbon\Carbon::parse($case->date_scheduled_docketed)->format('Y-m-d') : '-' }}
                        </td>
                        <td class="readonly-cell" data-field="aging_docket">{{ $case->aging_docket ?? '-' }}</td>
                        <td class="readonly-cell" data-field="status_docket">{{ $case->status_docket ?? '-' }}</td>
                        <td class="editable-cell" data-field="hearing_officer_mis" title="{{ $case->hearing_officer_mis ?? '' }}">
                            {{ $case->hearing_officer_mis ? Str::limit($case->hearing_officer_mis, 20) : '-' }}
                        </td>

                        {{-- Hearing Process Stage --}}
                        <td class="editable-cell" data-field="date_1st_mc_actual" data-type="date">
                            {{ $case->date_1st_mc_actual ? \Carbon\Carbon::parse($case->date_1st_mc_actual)->format('Y-m-d') : '-' }}
                        </td>
                        <td class="readonly-cell" data-field="first_mc_pct">{{ $case->first_mc_pct ?? '-' }}</td>
                        <td class="readonly-cell" data-field="status_1st_mc">{{ $case->status_1st_mc ?? '-' }}</td>
                        <td class="editable-cell" data-field="date_2nd_last_mc" data-type="date">
                            {{ $case->date_2nd_last_mc ? \Carbon\Carbon::parse($case->date_2nd_last_mc)->format('Y-m-d') : '-' }}
                        </td>
                        <td class="readonly-cell" data-field="second_last_mc_pct">{{ $case->second_last_mc_pct ?? '-' }}</td>
                        <td class="readonly-cell" data-field="status_2nd_mc">{{ $case->status_2nd_mc ?? '-' }}</td>
                        <td class="editable-cell" data-field="case_folder_forwarded_to_ro" data-type="date">
                            {{ $case->case_folder_forwarded_to_ro ? \Carbon\Carbon::parse($case->case_folder_forwarded_to_ro)->format('Y-m-d') : '-' }}
                        </td>

                        {{-- Review & Drafting --}}
                        <td class="readonly-cell" data-field="po_pct">
                            {{ $case->po_pct ? \Carbon\Carbon::parse($case->po_pct)->format('Y-m-d') : '-' }}
                        </td>
                        <td class="readonly-cell" data-field="aging_po_pct">{{ $case->aging_po_pct ?? '-' }}</td>
                        <td class="readonly-cell" data-field="status_po_pct">{{ $case->status_po_pct ?? '-' }}</td>

                        {{-- Orders & Disposition --}}
                        <td class="readonly-cell" data-field="pct_96_days">
                            {{ $case->pct_96_days ? \Carbon\Carbon::parse($case->pct_96_days)->format('Y-m-d') : '-' }}
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
                    <td colspan="32" class="text-center text-muted py-4">
                        <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                        No cases are currently assigned to MALSU.
                    </td>
                </tr>
            @endif
        </tbody>
    </table>
</div>