{{--
    Partial: sena_tab.blade.php
    Lists all Sena records directly — there is currently NO relationship to the
    `cases` table, so there is no establishment/case-name to display per row.
    ⚠️ See note at top of file: you'll likely want to add an identifying field
    before this goes live (see conversation notes).
--}}

@php
    $alertSuffix = $alertSuffix ?? 'SENA';
    $tableId     = $tableId ?? 'dataTableSENA';
    $searchId    = $searchId ?? 'customSearchSENA';
@endphp

<div class="alert alert-success alert-dismissible fade" role="alert" id="success-alert-tab{{ $alertSuffix }}" style="display: none;">
    <span id="success-message-tab{{ $alertSuffix }}"></span>
    <button type="button" class="close" onclick="hideAlert('success-alert-tab{{ $alertSuffix }}')">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
<div class="alert alert-danger alert-dismissible fade" role="alert" id="error-alert-tab{{ $alertSuffix }}" style="display: none;">
    <span id="error-message-tab{{ $alertSuffix }}"></span>
    <button type="button" class="close" onclick="hideAlert('error-alert-tab{{ $alertSuffix }}')">
        <span aria-hidden="true">&times;</span>
    </button>
</div>

<div class="d-flex justify-content-between align-items-center mb-3 custom-search-container">
    <div class="d-flex align-items-center">
        <label class="mr-2 mb-0" style="font-size: 0.8rem;">Search:</label>
        <input type="search"
               class="form-control form-control-sm"
               id="{{ $searchId }}"
               placeholder="Search SENA records..."
               style="width: 260px;">
    </div>
    <div class="d-flex align-items-center">
        <span class="badge badge-info mr-2" style="font-size: 0.85rem; padding: 0.45rem 0.8rem;">
            <i class="fas fa-gavel mr-1"></i>
            {{ $senaRecords->count() }} SENA record(s)
        </span>
    </div>
</div>

<div class="table-container">
    <table class="table table-bordered compact-table sticky-table cm-table"
           id="{{ $tableId }}"
           width="100%"
           cellspacing="0">
        <thead>
            <tr>
                <th>Actions</th>
                <th>No.</th>
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
            @if($senaRecords->count() > 0)
                @foreach($senaRecords as $sena)
                    <tr data-id="{{ $sena->id }}">
                        <td class="actions-cell collapsed">
                            <div class="action-buttons-container">
                                <button class="action-toggle-btn" type="button">
                                    <i class="fas fa-chevron-right"></i>
                                </button>
                                <div class="action-buttons">
                                    <button class="btn btn-warning btn-sm edit-row-btn-case"
                                            data-sena-id="{{ $sena->id }}"
                                            title="Edit Row">
                                        <i class="fas fa-edit"></i>
                                    </button>

                                    <button type="button"
                                            class="btn btn-danger btn-sm delete-btn"
                                            data-sena-id="{{ $sena->id }}"
                                            title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </td>

                        <td class="readonly-cell">{{ $loop->iteration }}</td>

                        <td class="editable-cell" data-field="regional_docket_number" style="background-color: #fff3cd !important;">
                            {{ $sena->regional_docket_number ?? '-' }}
                        </td>
                        <td class="editable-cell" data-field="sheriff_designate">
                            {{ $sena->sheriff_designate ?? '-' }}
                        </td>
                        <td class="editable-cell" data-field="date_compliance_order" data-type="date">
                            {{ $sena->date_compliance_order ? \Carbon\Carbon::parse($sena->date_compliance_order)->format('Y-m-d') : '-' }}
                        </td>
                        <td class="editable-cell" data-field="total_gls_monetary_award">
                            {{ $sena->total_gls_monetary_award ?? '-' }}
                        </td>
                        <td class="editable-cell" data-field="total_workers_benefited">
                            {{ $sena->total_workers_benefited ?? '-' }}
                        </td>
                        <td class="editable-cell" data-field="amount_penalty_double_indemnity">
                            {{ $sena->amount_penalty_double_indemnity ?? '-' }}
                        </td>
                        <td class="editable-cell" data-field="voluntary_compliance">
                            {{ $sena->voluntary_compliance ?? '-' }}
                        </td>
                        <td class="editable-cell" data-field="action_taken">
                            {{ $sena->action_taken ?? '-' }}
                        </td>
                        <td class="editable-cell" data-field="total_gls_monetary_satisfied">
                            {{ $sena->total_gls_monetary_satisfied ?? '-' }}
                        </td>
                        <td class="editable-cell" data-field="total_workers_satisfied">
                            {{ $sena->total_workers_satisfied ?? '-' }}
                        </td>
                        <td class="editable-cell" data-field="complied_oshs_violations">
                            {{ $sena->complied_oshs_violations ?? '-' }}
                        </td>
                        <td class="editable-cell" data-field="total_penalty_double_indemnity_collected">
                            {{ $sena->total_penalty_double_indemnity_collected ?? '-' }}
                        </td>
                        <td class="editable-cell" data-field="total_oshs_penalty_admin_fines_collected">
                            {{ $sena->total_oshs_penalty_admin_fines_collected ?? '-' }}
                        </td>
                        <td class="editable-cell" data-field="total_workers_absorbed">
                            {{ $sena->total_workers_absorbed ?? '-' }}
                        </td>
                        <td class="editable-cell" data-field="full_or_partial">
                            {{ $sena->full_or_partial ?? '-' }}
                        </td>
                        <td class="editable-cell" data-field="date_writ_of_execution_served" data-type="date">
                            {{ $sena->date_writ_of_execution_served ? \Carbon\Carbon::parse($sena->date_writ_of_execution_served)->format('Y-m-d') : '-' }}
                        </td>
                        <td class="editable-cell" data-field="date_indorsed_to_po" data-type="date">
                            {{ $sena->date_indorsed_to_po ? \Carbon\Carbon::parse($sena->date_indorsed_to_po)->format('Y-m-d') : '-' }}
                        </td>
                        <td class="editable-cell" data-field="po_date_received" data-type="date">
                            {{ $sena->po_date_received ? \Carbon\Carbon::parse($sena->po_date_received)->format('Y-m-d') : '-' }}
                        </td>
                        <td class="editable-cell" data-field="ro_received_sheriffs_return" data-type="date">
                            {{ $sena->ro_received_sheriffs_return ? \Carbon\Carbon::parse($sena->ro_received_sheriffs_return)->format('Y-m-d') : '-' }}
                        </td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="20" class="text-center text-muted py-4">
                        <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                        No SENA records yet.
                    </td>
                </tr>
            @endif
        </tbody>
    </table>
</div>