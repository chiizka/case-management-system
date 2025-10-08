<!-- Success/Error alerts for AJAX -->
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

<!-- Search + Buttons Row -->
<div class="d-flex justify-content-between align-items-center mb-3 custom-search-container">
    <div class="d-flex align-items-center">
        <label class="mr-2 mb-0" style="font-size: 0.8rem;">Search:</label>
        <input type="search" class="form-control form-control-sm" id="customSearch1" placeholder="Search inspections..." style="width: 200px;">
    </div>
</div>

<!-- Table Container -->
<div class="table-container">
    <table class="table table-bordered compact-table sticky-table" id="dataTable1" width="100%" cellspacing="0">
        <thead>
            <tr>
                <th>Inspection ID</th>
                <th>Name of Establishment</th>
                <th>PO Office</th>
                <th>Inspector Name</th>
                <th>Inspector Authority No</th>
                <th>Date of Inspection</th>
                <th>Date of NR</th>
                <th>Lapse 20 Day Period</th>
                <th>TWG ALI</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @if(isset($inspections) && $inspections->count() > 0)
                @foreach($inspections as $inspection)
                    <tr data-id="{{ $inspection->id }}">
                        <td class="editable-cell readonly-cell" data-field="inspection_id" title="From case record">{{ $inspection->case->inspection_id ?? '-' }}</td>
                        <td class="editable-cell readonly-cell" data-field="establishment_name" title="{{ $inspection->case->establishment_name ?? '' }}">
                            {{ $inspection->case ? Str::limit($inspection->case->establishment_name, 25) : '-' }}
                        </td>
                        <td class="editable-cell" data-field="po_office">{{ $inspection->po_office ?? '-' }}</td>
                        <td class="editable-cell" data-field="inspector_name">{{ $inspection->inspector_name ?? '-' }}</td>
                        <td class="editable-cell" data-field="inspector_authority_no">{{ $inspection->inspector_authority_no ?? '-' }}</td>
                        <td class="editable-cell" data-field="date_of_inspection" data-type="date">{{ $inspection->date_of_inspection ? \Carbon\Carbon::parse($inspection->date_of_inspection)->format('Y-m-d') : '-' }}</td>
                        <td class="editable-cell" data-field="date_of_nr" data-type="date">{{ $inspection->date_of_nr ? \Carbon\Carbon::parse($inspection->date_of_nr)->format('Y-m-d') : '-' }}</td>
                        <td class="editable-cell readonly-cell" data-field="lapse_20_day_period" data-type="date" title="Auto-calculated: 20 days after Date of NR">
                            {{ $inspection->lapse_20_day_period ? \Carbon\Carbon::parse($inspection->lapse_20_day_period)->format('Y-m-d') : '-' }}
                        </td>
                        <td class="editable-cell" data-field="twg_ali">{{ $inspection->twg_ali ?? '-' }}</td>
                        <td>
                            <a href="{{ route('inspection.show', $inspection->id) }}" class="btn btn-info btn-sm" title="View">
                                <i class="fas fa-eye"></i>
                            </a>
                            <button class="btn btn-warning btn-sm edit-row-btn" title="Edit Row">
                                <i class="fas fa-edit"></i>
                            </button>
                            <form action="{{ route('inspection.destroy', $inspection->id) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm inspection-delete-btn" title="Delete" onclick="return confirm('Are you sure you want to delete this inspection?')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                            
                            @if($inspection->case && $inspection->case->current_stage === '1: Inspections')
                                <form action="{{ route('case.nextStage', $inspection->case->id) }}" method="POST" style="display:inline;">
                                    @csrf
                                    <button type="submit" class="btn btn-success btn-sm ml-1" title="Move to Docketing" onclick="return confirm('Complete inspection and move to Docketing?')">
                                        <i class="fas fa-arrow-right"></i> Next
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="10" class="text-center">No inspections found.</td>
                </tr>
            @endif
        </tbody>
    </table>
</div>
