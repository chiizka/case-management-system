<div class="alert alert-success alert-dismissible fade" role="alert" id="success-alert-tab6" style="display: none;">
    <span id="success-message-tab6"></span>
    <button type="button" class="close" onclick="hideAlert('success-alert-tab6')">
        <span aria-hidden="true">&times;</span>
    </button>
</div>

<div class="alert alert-danger alert-dismissible fade" role="alert" id="error-alert-tab6" style="display: none;">
    <span id="error-message-tab6"></span>
    <button type="button" class="close" onclick="hideAlert('error-alert-tab6')">
        <span aria-hidden="true">&times;</span>
    </button>
</div>

<div class="d-flex justify-content-between align-items-center mb-3 custom-search-container">
    <div class="d-flex align-items-center">
        <label class="mr-2 mb-0" style="font-size: 0.8rem;">Search:</label>
        <input type="search" class="form-control form-control-sm" id="customSearch6" placeholder="Search compliance & awards..." style="width: 200px;">
    </div>
</div>

<div class="table-container">
    <table class="table table-bordered compact-table sticky-table" id="dataTable6" width="100%" cellspacing="0">
        <thead>
            <tr>
                <th>Inspection ID</th>
                <th>Establishment Name</th>
                <th>Compliance Order/Monetary Award</th>
                <th>OSH Penalty</th>
                <th>Affected Male</th>
                <th>Affected Female</th>
                <th>First Order Dismissal CNPC</th>
                <th>Tavable <10 Workers</th>
                <th>With Deposited Monetary Claims</th>
                <th>Amount Deposited</th>
                <th>With Order Payment Notice</th>
                <th>Status All Employees Received</th>
                <th>Status Case After First Order</th>
                <th>Date Notice Finality/Dismissed</th>
                <th>Released Date Notice Finality</th>
                <th>Updated/Ticked in MIS</th>
                <th>Second Order Drafter</th>
                <th>Date Received by Drafter CT/CNPC</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @if(isset($complianceAndAwards) && $complianceAndAwards->count() > 0)
                @foreach($complianceAndAwards as $compliance)
                    <tr data-id="{{ $compliance->id }}">
                        <td class="editable-cell readonly-cell" data-field="inspection_id">{{ $compliance->case->inspection_id ?? '-' }}</td>
                        <td class="editable-cell readonly-cell" data-field="establishment_name" title="{{ $compliance->case->establishment_name ?? '' }}">
                            {{ $compliance->case ? Str::limit($compliance->case->establishment_name, 25) : '-' }}
                        </td>
                        <td class="editable-cell" data-field="compliance_order_monetary_award">{{ $compliance->compliance_order_monetary_award ? number_format($compliance->compliance_order_monetary_award, 2) : '-' }}</td>
                        <td class="editable-cell" data-field="osh_penalty">{{ $compliance->osh_penalty ? number_format($compliance->osh_penalty, 2) : '-' }}</td>
                        <td class="editable-cell" data-field="affected_male">{{ $compliance->affected_male ?? '-' }}</td>
                        <td class="editable-cell" data-field="affected_female">{{ $compliance->affected_female ?? '-' }}</td>
                        <td class="editable-cell" data-field="first_order_dismissal_cnpc" data-type="select">
                            @if($compliance->first_order_dismissal_cnpc !== null)
                                <span class="badge badge-{{ $compliance->first_order_dismissal_cnpc == 1 ? 'success' : 'warning' }}">
                                    {{ $compliance->first_order_dismissal_cnpc == 1 ? 'Yes' : 'No' }}
                                </span>
                            @else
                                -
                            @endif
                        </td>
                        <td class="editable-cell" data-field="tavable_less_than_10_workers" data-type="select">
                            @if($compliance->tavable_less_than_10_workers !== null)
                                <span class="badge badge-{{ $compliance->tavable_less_than_10_workers == 1 ? 'success' : 'warning' }}">
                                    {{ $compliance->tavable_less_than_10_workers == 1 ? 'Yes' : 'No' }}
                                </span>
                            @else
                                -
                            @endif
                        </td>
                        <td class="editable-cell" data-field="with_deposited_monetary_claims" data-type="select">
                            @if($compliance->with_deposited_monetary_claims !== null)
                                <span class="badge badge-{{ $compliance->with_deposited_monetary_claims == 1 ? 'success' : 'warning' }}">
                                    {{ $compliance->with_deposited_monetary_claims == 1 ? 'Yes' : 'No' }}
                                </span>
                            @else
                                -
                            @endif
                        </td>
                        <td class="editable-cell" data-field="amount_deposited">{{ $compliance->amount_deposited ? number_format($compliance->amount_deposited, 2) : '-' }}</td>
                        <td class="editable-cell" data-field="with_order_payment_notice" data-type="select">
                            @if($compliance->with_order_payment_notice !== null)
                                <span class="badge badge-{{ $compliance->with_order_payment_notice == 1 ? 'success' : 'warning' }}">
                                    {{ $compliance->with_order_payment_notice == 1 ? 'Yes' : 'No' }}
                                </span>
                            @else
                                -
                            @endif
                        </td>
                        <td class="editable-cell" data-field="status_all_employees_received" data-type="select">
                            @if($compliance->status_all_employees_received)
                                <span class="badge badge-{{ $compliance->status_all_employees_received == 'Yes' ? 'success' : ($compliance->status_all_employees_received == 'No' ? 'danger' : 'warning') }}">
                                    {{ $compliance->status_all_employees_received }}
                                </span>
                            @else
                                -
                            @endif
                        </td>
                        <td class="editable-cell" data-field="status_case_after_first_order">{{ $compliance->status_case_after_first_order ?? '-' }}</td>
                        <td class="editable-cell" data-field="date_notice_finality_dismissed" data-type="date">{{ $compliance->date_notice_finality_dismissed ? \Carbon\Carbon::parse($compliance->date_notice_finality_dismissed)->format('Y-m-d') : '-' }}</td>
                        <td class="editable-cell" data-field="released_date_notice_finality" data-type="date">{{ $compliance->released_date_notice_finality ? \Carbon\Carbon::parse($compliance->released_date_notice_finality)->format('Y-m-d') : '-' }}</td>
                        <td class="editable-cell" data-field="updated_ticked_in_mis" data-type="select">
                            @if($compliance->updated_ticked_in_mis !== null)
                                <span class="badge badge-{{ $compliance->updated_ticked_in_mis == 1 ? 'success' : 'warning' }}">
                                    {{ $compliance->updated_ticked_in_mis == 1 ? 'Yes' : 'No' }}
                                </span>
                            @else
                                -
                            @endif
                        </td>
                        <td class="editable-cell" data-field="second_order_drafter">{{ $compliance->second_order_drafter ?? '-' }}</td>
                        <td class="editable-cell" data-field="date_received_by_drafter_ct_cnpc" data-type="date">{{ $compliance->date_received_by_drafter_ct_cnpc ? \Carbon\Carbon::parse($compliance->date_received_by_drafter_ct_cnpc)->format('Y-m-d') : '-' }}</td>
                        <td>
                            <button class="btn btn-warning btn-sm edit-row-btn-compliance" title="Edit Row">
                                <i class="fas fa-edit"></i>
                            </button>
                            <form action="{{ route('compliance-and-awards.destroy', $compliance->id) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" title="Delete" onclick="return confirm('Are you sure?')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                            
                            @if($compliance->case && $compliance->case->current_stage === '6: Compliance & Awards')
                                <form action="{{ route('case.nextStage', $compliance->case->id) }}" method="POST" style="display:inline;">
                                    @csrf
                                    <button type="submit" class="btn btn-success btn-sm ml-1" title="Move to Appeals & Resolution" onclick="return confirm('Complete compliance & awards and move to Appeals & Resolution?')">
                                        <i class="fas fa-arrow-right"></i> Next
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="19" class="text-center">No compliance & awards records found.</td>
                </tr>
            @endif
        </tbody>
    </table>
</div>