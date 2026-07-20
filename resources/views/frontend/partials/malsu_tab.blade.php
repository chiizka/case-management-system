{{-- 
    Partial: malsu_tab.blade.php
    Shows cases currently located at the MALSU role.
--}}

@php
    $alertSuffix = $alertSuffix ?? 'MALSU';
    $tableId     = $tableId ?? 'dataTableMALSU';
    $searchId    = $searchId ?? 'customSearchMALSU';
    $badgeLabel  = $badgeLabel ?? (Auth::user()->isSheriff() ? Auth::user()->getSheriffProvinceName() . ' Sheriff' : 'MALSU');
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
               placeholder="Search cases..."
               style="width: 260px;">
    </div>
    <div class="d-flex align-items-center">
        <span class="badge badge-info mr-2" style="font-size: 0.85rem; padding: 0.45rem 0.8rem;">
            <i class="fas fa-folder-open mr-1"></i>
            {{ $cases->count() }} case(s) currently with {{ $badgeLabel }}
        </span>

        {{-- Dummy button — not wired up to anything yet --}}
        <button type="button" class="btn btn-success btn-sm" id="malsuUploadExcelBtn" title="Upload Excel (coming soon)">
            <i class="fas fa-file-excel mr-1"></i> Upload Excel
        </button>
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
                @unless(Auth::user()->isSheriff())
                    <th>Sheriff Reports</th>
                @endunless
            </tr>
        </thead>
        <tbody>
            @if($cases->count() > 0)
                @foreach($cases as $malsu)
                @php $case = $malsu->case; @endphp
                    <tr data-id="{{ $malsu->id }}" data-has-case="{{ $case ? '1' : '0' }}">
                        <td class="actions-cell collapsed">
                            <div class="action-buttons-container">
                                <button class="action-toggle-btn" type="button">
                                    <i class="fas fa-chevron-right"></i>
                                </button>
                                <div class="action-buttons">
                                    <button class="btn btn-warning btn-sm edit-row-btn-case"
                                            data-case-id="{{ $malsu->id }}"
                                            title="Edit Row">
                                        <i class="fas fa-edit"></i>
                                    </button>

                                    @if($case)
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
                                    @endif

                                        @if(Auth::user()->isSheriff())
                                            @if($case)
                                                <button type="button"
                                                        class="btn btn-primary btn-sm upload-report-btn"
                                                        data-case-id="{{ $case->id }}"
                                                        data-case-no="{{ $case->case_no ?? 'N/A' }}"
                                                        data-establishment="{{ $case->establishment_name ?? 'N/A' }}"
                                                        title="Upload Report">
                                                    <i class="fas fa-upload"></i>
                                                </button>
                                            @else
                                                <span class="text-muted small" title="Legacy record — no linked case for reports">
                                                    <i class="fas fa-ban"></i>
                                                </span>
                                            @endif
                                        @else
                                        @if($case)
                                            <button type="button"
                                                    class="btn btn-success btn-sm complete-case-btn"
                                                    data-case-id="{{ $case->id }}"
                                                    data-case-no="{{ $case->case_no ?? 'N/A' }}"
                                                    data-establishment="{{ $case->establishment_name ?? 'N/A' }}"
                                                    data-stage="{{ explode(': ', $case->current_stage)[1] ?? $case->current_stage ?? 'Unknown' }}"
                                                    title="Mark as Complete">
                                                <i class="fas fa-check-circle"></i>
                                            </button>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        </td>

                        {{-- From cases table --}}
                        <td class="readonly-cell">{{ $case?->no ?? '-' }}</td>
                        <td class="readonly-cell wrap-cell" style="background-color: #d1ecf1 !important;">
                            <span>{{ $case->establishment_name ?? $malsu->case_title ?? '-' }}</span>
                            @if(!$case)
                                <span class="badge badge-secondary ml-1" style="font-size:0.65rem;">Legacy Record</span>
                            @endif
                            @if($case?->establishment_address)
                                <br><small class="text-muted address-subtext"
                                        style="font-weight: normal; font-size: 0.75rem;">
                                    {{ $case->establishment_address }}
                                </small>
                            @endif
                            {{-- Replace the existing @if($case->documentTracking && ...) badge block --}}
                            @if($case?->documentTracking)
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
                                @unless(Auth::user()->isSheriff())
                                    {{-- Editable select (shown in edit mode) --}}
                                    <select class="form-control form-control-sm case-tag-select editable-input mt-1"
                                            data-field="case_tag"
                                            style="display:none; min-width: 160px;">
                                        <option value="">— No Tag —</option>
                                        <option value="For Execution"              {{ $currentTag === 'For Execution'              ? 'selected' : '' }}>For Execution</option>
                                        <option value="Motion for Reconsideration" {{ $currentTag === 'Motion for Reconsideration' ? 'selected' : '' }}>Motion for Reconsideration</option>
                                    </select>
                                @endunless
                            @endif
                        </td>

                        {{-- From malsu table --}}
                        @if(Auth::user()->isSheriff())
                            <td class="readonly-cell" data-field="regional_docket_number" style="background-color: #fff3cd !important;">
                                {{ $malsu->regional_docket_number ?? '-' }}
                            </td>
                        @else
                            <td class="editable-cell" data-field="regional_docket_number" style="background-color: #fff3cd !important;">
                                {{ $malsu->regional_docket_number ?? '-' }}
                            </td>
                        @endif
                        @if(Auth::user()->isSheriff())
                            <td class="readonly-cell" data-field="sheriff_designate">
                                {{ $malsu->sheriff_designate ?? '-' }}
                            </td>
                        @else
                            <td class="editable-cell" data-field="sheriff_designate" data-type="select">
                                {{ $malsu->sheriff_designate ?? '-' }}
                            </td>
                        @endif
                        <td class="editable-cell" data-field="date_compliance_order" data-type="date">
                            {{ $malsu->date_compliance_order ? \Carbon\Carbon::parse($malsu->date_compliance_order)->format('Y-m-d') : '-' }}
                        </td>
                        <td class="editable-cell" data-field="total_gls_monetary_award">
                            {{ $malsu->total_gls_monetary_award ?? '-' }}
                        </td>
                        <td class="editable-cell" data-field="total_workers_benefited">
                            {{ $malsu->total_workers_benefited ?? '-' }}
                        </td>
                        <td class="editable-cell" data-field="amount_penalty_double_indemnity">
                            {{ $malsu->amount_penalty_double_indemnity ?? '-' }}
                        </td>
                        <td class="editable-cell" data-field="voluntary_compliance">
                            {{ $malsu->voluntary_compliance ?? '-' }}
                        </td>
                        <td class="editable-cell" data-field="action_taken">
                            {{ $malsu->action_taken ?? '-' }}
                        </td>
                        <td class="editable-cell" data-field="total_gls_monetary_satisfied">
                            {{ $malsu->total_gls_monetary_satisfied ?? '-' }}
                        </td>
                        <td class="editable-cell" data-field="total_workers_satisfied">
                            {{ $malsu->total_workers_satisfied ?? '-' }}
                        </td>
                        <td class="editable-cell" data-field="complied_oshs_violations">
                            {{ $malsu->complied_oshs_violations ?? '-' }}
                        </td>
                        <td class="editable-cell" data-field="total_penalty_double_indemnity_collected">
                            {{ $malsu->total_penalty_double_indemnity_collected ?? '-' }}
                        </td>
                        <td class="editable-cell" data-field="total_oshs_penalty_admin_fines_collected">
                            {{ $malsu->total_oshs_penalty_admin_fines_collected ?? '-' }}
                        </td>
                        <td class="editable-cell" data-field="total_workers_absorbed">
                            {{ $malsu->total_workers_absorbed ?? '-' }}
                        </td>
                        <td class="editable-cell" data-field="full_or_partial">
                            {{ $malsu->full_or_partial ?? '-' }}
                        </td>
                        <td class="editable-cell" data-field="date_writ_of_execution_served" data-type="date">
                            {{ $malsu->date_writ_of_execution_served ? \Carbon\Carbon::parse($malsu->date_writ_of_execution_served)->format('Y-m-d') : '-' }}
                        </td>
                        <td class="editable-cell" data-field="date_indorsed_to_po" data-type="date">
                            {{ $malsu->date_indorsed_to_po ? \Carbon\Carbon::parse($malsu->date_indorsed_to_po)->format('Y-m-d') : '-' }}
                        </td>
                        <td class="editable-cell" data-field="po_date_received" data-type="date">
                            {{ $malsu->po_date_received ? \Carbon\Carbon::parse($malsu->po_date_received)->format('Y-m-d') : '-' }}
                        </td>
                        <td class="editable-cell" data-field="ro_received_sheriffs_return" data-type="date">
                            {{ $malsu->ro_received_sheriffs_return ? \Carbon\Carbon::parse($malsu->ro_received_sheriffs_return)->format('Y-m-d') : '-' }}
                        </td>
                        @unless(Auth::user()->isSheriff())
                            <td class="readonly-cell wrap-cell">
                                @php $reportCount = $malsu->sheriffsReports->count(); @endphp
                                @if($reportCount > 0)
                                    <button type="button" class="btn btn-sm btn-outline-primary view-reports-grid-btn"
                                            data-case-id="{{ $case->id ?? '' }}"
                                            data-malsu-id="{{ $malsu->id }}"
                                            data-case-no="{{ $case->case_no ?? 'N/A' }}"
                                            data-establishment="{{ $case->establishment_name ?? $malsu->case_title ?? 'N/A' }}">
                                        <i class="fas fa-table"></i> {{ $reportCount }} report(s)
                                    </button>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        @endunless
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="{{ Auth::user()->isSheriff() ? 22 : 23 }}" class="text-center text-muted py-4">
                        <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                        No cases are currently assigned.
                    </td>
                </tr>
            @endif
        </tbody>
    </table>
</div>