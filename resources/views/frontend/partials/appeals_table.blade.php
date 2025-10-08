<div class="alert alert-success alert-dismissible fade" role="alert" id="success-alert-tab7" style="display: none;">
    <span id="success-message-tab7"></span>
    <button type="button" class="close" onclick="hideAlert('success-alert-tab7')">
        <span aria-hidden="true">&times;</span>
    </button>
</div>

<div class="alert alert-danger alert-dismissible fade" role="alert" id="error-alert-tab7" style="display: none;">
    <span id="error-message-tab7"></span>
    <button type="button" class="close" onclick="hideAlert('error-alert-tab7')">
        <span aria-hidden="true">&times;</span>
    </button>
</div>

<div class="d-flex justify-content-between align-items-center mb-3 custom-search-container">
    <div class="d-flex align-items-center">
        <label class="mr-2 mb-0" style="font-size: 0.8rem;">Search:</label>
        <input type="search" class="form-control form-control-sm" id="customSearch7" placeholder="Search appeals & resolution..." style="width: 200px;">
    </div>
</div>

<div class="table-container">
    <table class="table table-bordered compact-table sticky-table" id="dataTable7" width="100%" cellspacing="0">
        <thead>
            <tr>
                <th>Inspection ID</th>
                <th>Establishment Name</th>
                <th>Date Returned Case Mgmt</th>
                <th>Review CT/CNPC</th>
                <th>Date Received Drafter Finalization 2nd</th>
                <th>Date Returned Case Mgmt Signature 2nd</th>
                <th>Date Order 2nd CNPC</th>
                <th>Released Date 2nd CNPC</th>
                <th>Date Forwarded MALSU</th>
                <th>Motion Reconsideration Date</th>
                <th>Date Received MALSU</th>
                <th>Date Resolution MR</th>
                <th>Released Date Resolution MR</th>
                <th>Date Appeal Received Records</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @if(isset($appealsAndResolutions) && $appealsAndResolutions->count() > 0)
                @foreach($appealsAndResolutions as $appeal)
                    <tr data-id="{{ $appeal->id }}">
                        <td class="editable-cell readonly-cell" data-field="inspection_id">{{ $appeal->case->inspection_id ?? '-' }}</td>
                        <td class="editable-cell readonly-cell" data-field="establishment_name" title="{{ $appeal->case->establishment_name ?? '' }}">
                            {{ $appeal->case ? Str::limit($appeal->case->establishment_name, 25) : '-' }}
                        </td>
                        <td class="editable-cell" data-field="date_returned_case_mgmt" data-type="date">{{ $appeal->date_returned_case_mgmt ? \Carbon\Carbon::parse($appeal->date_returned_case_mgmt)->format('Y-m-d') : '-' }}</td>
                        <td class="editable-cell" data-field="review_ct_cnpc">{{ $appeal->review_ct_cnpc ?? '-' }}</td>
                        <td class="editable-cell" data-field="date_received_drafter_finalization_2nd" data-type="date">{{ $appeal->date_received_drafter_finalization_2nd ? \Carbon\Carbon::parse($appeal->date_received_drafter_finalization_2nd)->format('Y-m-d') : '-' }}</td>
                        <td class="editable-cell" data-field="date_returned_case_mgmt_signature_2nd" data-type="date">{{ $appeal->date_returned_case_mgmt_signature_2nd ? \Carbon\Carbon::parse($appeal->date_returned_case_mgmt_signature_2nd)->format('Y-m-d') : '-' }}</td>
                        <td class="editable-cell" data-field="date_order_2nd_cnpc" data-type="date">{{ $appeal->date_order_2nd_cnpc ? \Carbon\Carbon::parse($appeal->date_order_2nd_cnpc)->format('Y-m-d') : '-' }}</td>
                        <td class="editable-cell" data-field="released_date_2nd_cnpc" data-type="date">{{ $appeal->released_date_2nd_cnpc ? \Carbon\Carbon::parse($appeal->released_date_2nd_cnpc)->format('Y-m-d') : '-' }}</td>
                        <td class="editable-cell" data-field="date_forwarded_malsu" data-type="date">{{ $appeal->date_forwarded_malsu ? \Carbon\Carbon::parse($appeal->date_forwarded_malsu)->format('Y-m-d') : '-' }}</td>
                        <td class="editable-cell" data-field="motion_reconsideration_date" data-type="date">{{ $appeal->motion_reconsideration_date ? \Carbon\Carbon::parse($appeal->motion_reconsideration_date)->format('Y-m-d') : '-' }}</td>
                        <td class="editable-cell" data-field="date_received_malsu" data-type="date">{{ $appeal->date_received_malsu ? \Carbon\Carbon::parse($appeal->date_received_malsu)->format('Y-m-d') : '-' }}</td>
                        <td class="editable-cell" data-field="date_resolution_mr" data-type="date">{{ $appeal->date_resolution_mr ? \Carbon\Carbon::parse($appeal->date_resolution_mr)->format('Y-m-d') : '-' }}</td>
                        <td class="editable-cell" data-field="released_date_resolution_mr" data-type="date">{{ $appeal->released_date_resolution_mr ? \Carbon\Carbon::parse($appeal->released_date_resolution_mr)->format('Y-m-d') : '-' }}</td>
                        <td class="editable-cell" data-field="date_appeal_received_records" data-type="date">{{ $appeal->date_appeal_received_records ? \Carbon\Carbon::parse($appeal->date_appeal_received_records)->format('Y-m-d') : '-' }}</td>
                        <td>
                            <button class="btn btn-warning btn-sm edit-row-btn-appeals" title="Edit Row">
                                <i class="fas fa-edit"></i>
                            </button>
                            <form action="{{ route('appeals-and-resolution.destroy', $appeal->id) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" title="Delete" onclick="return confirm('Are you sure?')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                            
                            @if($appeal->case && $appeal->case->current_stage === '7: Appeals & Resolution')
                                <form action="{{ route('case.nextStage', $appeal->case->id) }}" method="POST" style="display:inline;">
                                    @csrf
                                    <button type="submit" class="btn btn-success btn-sm ml-1" title="Complete Case" onclick="return confirm('Mark this case as completed?')">
                                        <i class="fas fa-check"></i> Complete
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="15" class="text-center">No appeals & resolution records found.</td>
                </tr>
            @endif
        </tbody>
    </table>
</div>