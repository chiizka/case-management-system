{{-- 
    Partial: malsu_tab.blade.php
    Shows cases currently located at the MALSU role.
--}}

<div class="alert alert-success alert-dismissible fade" role="alert" id="success-alert-tabMALSU" style="display: none;">
    <span id="success-message-tabMALSU"></span>
    <button type="button" class="close" onclick="hideAlert('success-alert-tabMALSU')">
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
                <th style="background-color: #d1ecf1 !important;">Case Title / Establishment Name</th>
                <th style="background-color: #fff3cd !important;">Regional Docket No.</th>
                <th>Sheriff Designate</th>
                <th>Date of Compliance Order / Resolution</th>
                <th>Total GLS Monetary Award</th>
                <th>Total No. of Workers Benefited</th>
                <th>Amount of Penalty for Double Indemnity</th>
                <th>Voluntary Compliance</th>
                <th>Action Taken</th>
                <th>Total GLS Monetary Award Satisfied</th>
                <th>Total No. of Workers Satisfied</th>
                <th>Complied OSHS Violations</th>
                <th>Total Amount of Penalty for Double Indemnity Collected</th>
                <th>Total OSHS Penalty / Administrative Fines Collected</th>
                <th>Total No. of Absorbed Workers</th>
                <th>Full or Partial</th>
                <th>Serves Writ of Execution</th>
                <th>Date Indorsed to PO</th>
                <th>PO Date Received</th>
                <th>RO Received Sheriff's Return</th>
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

                        {{-- From cases table --}}
                        <td class="readonly-cell">{{ $case->no ?? '-' }}</td>
                        <td class="readonly-cell wrap-cell" style="background-color: #d1ecf1 !important;">
                            <span>{{ $case->establishment_name ?? '-' }}</span>
                            @if($case->establishment_address)
                                <br><small class="text-muted address-subtext"
                                        style="font-weight: normal; font-size: 0.75rem;">
                                    {{ $case->establishment_address }}
                                </small>
                            @endif
                            {{-- Replace the existing @if($case->documentTracking && ...) badge block --}}
                            @if($case->documentTracking)
                                <br>
                                @php
                                    $tagColors = [
                                        'For Execution'              => 'danger',
                                        'Motion for Reconsideration' => 'warning',
                                    ];
                                    $currentTag = $case->documentTracking->case_tag ?? '';
                                    $tagColor   = $tagColors[$currentTag] ?? 'secondary';
                                @endphp

                                {{-- Display badge (shown in read mode) --}}
                                <span class="case-tag-badge badge badge-{{ $tagColor }} mt-1"
                                    style="font-size: 0.7rem; {{ $currentTag ? '' : 'display:none;' }}"
                                    data-tag="{{ $currentTag }}">
                                    <i class="fas fa-bolt mr-1"></i>
                                    {{ strtoupper($currentTag) }}
                                </span>

                                {{-- Editable select (shown in edit mode) --}}
                                <select class="form-control form-control-sm case-tag-select editable-input mt-1"
                                        data-field="case_tag"
                                        style="display:none; min-width: 160px;">
                                    <option value="">— No Tag —</option>
                                    <option value="For Execution"              {{ $currentTag === 'For Execution'              ? 'selected' : '' }}>For Execution</option>
                                    <option value="Motion for Reconsideration" {{ $currentTag === 'Motion for Reconsideration' ? 'selected' : '' }}>Motion for Reconsideration</option>
                                </select>
                            @endif
                        </td>

                        {{-- From malsu table --}}
                        <td class="editable-cell" data-field="regional_docket_number" style="background-color: #fff3cd !important;">
                            {{ $case->malsu->regional_docket_number ?? '-' }}
                        </td>
                        <td class="editable-cell" data-field="sheriff_designate" data-type="select">
                            {{ $case->malsu->sheriff_designate ?? '-' }}
                        </td>
                        <td class="editable-cell" data-field="date_compliance_order" data-type="date">
                            {{ $case->malsu?->date_compliance_order ? \Carbon\Carbon::parse($case->malsu->date_compliance_order)->format('Y-m-d') : '-' }}
                        </td>
                        <td class="editable-cell" data-field="total_gls_monetary_award">
                            {{ $case->malsu->total_gls_monetary_award ?? '-' }}
                        </td>
                        <td class="editable-cell" data-field="total_workers_benefited">
                            {{ $case->malsu->total_workers_benefited ?? '-' }}
                        </td>
                        <td class="editable-cell" data-field="amount_penalty_double_indemnity">
                            {{ $case->malsu->amount_penalty_double_indemnity ?? '-' }}
                        </td>
                        <td class="editable-cell" data-field="voluntary_compliance">
                            {{ $case->malsu->voluntary_compliance ?? '-' }}
                        </td>
                        <td class="editable-cell" data-field="action_taken">
                            {{ $case->malsu->action_taken ?? '-' }}
                        </td>
                        <td class="editable-cell" data-field="total_gls_monetary_satisfied">
                            {{ $case->malsu->total_gls_monetary_satisfied ?? '-' }}
                        </td>
                        <td class="editable-cell" data-field="total_workers_satisfied">
                            {{ $case->malsu->total_workers_satisfied ?? '-' }}
                        </td>
                        <td class="editable-cell" data-field="complied_oshs_violations">
                            {{ $case->malsu->complied_oshs_violations ?? '-' }}
                        </td>
                        <td class="editable-cell" data-field="total_penalty_double_indemnity_collected">
                            {{ $case->malsu->total_penalty_double_indemnity_collected ?? '-' }}
                        </td>
                        <td class="editable-cell" data-field="total_oshs_penalty_admin_fines_collected">
                            {{ $case->malsu->total_oshs_penalty_admin_fines_collected ?? '-' }}
                        </td>
                        <td class="editable-cell" data-field="total_workers_absorbed">
                            {{ $case->malsu->total_workers_absorbed ?? '-' }}
                        </td>
                        <td class="editable-cell" data-field="full_or_partial">
                            {{ $case->malsu->full_or_partial ?? '-' }}
                        </td>
                        <td class="editable-cell" data-field="date_writ_of_execution_served" data-type="date">
                            {{ $case->malsu?->date_writ_of_execution_served ? \Carbon\Carbon::parse($case->malsu->date_writ_of_execution_served)->format('Y-m-d') : '-' }}
                        </td>
                        <td class="editable-cell" data-field="date_indorsed_to_po" data-type="date">
                            {{ $case->malsu?->date_indorsed_to_po ? \Carbon\Carbon::parse($case->malsu->date_indorsed_to_po)->format('Y-m-d') : '-' }}
                        </td>
                        <td class="editable-cell" data-field="po_date_received" data-type="date">
                            {{ $case->malsu?->po_date_received ? \Carbon\Carbon::parse($case->malsu->po_date_received)->format('Y-m-d') : '-' }}
                        </td>
                        <td class="editable-cell" data-field="ro_received_sheriffs_return" data-type="date">
                            {{ $case->malsu?->ro_received_sheriffs_return ? \Carbon\Carbon::parse($case->malsu->ro_received_sheriffs_return)->format('Y-m-d') : '-' }}
                        </td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="22" class="text-center text-muted py-4">
                        <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                        No cases are currently assigned to MALSU.
                    </td>
                </tr>
            @endif
        </tbody>
    </table>
</div>