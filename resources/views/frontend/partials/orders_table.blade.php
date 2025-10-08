<div class="alert alert-success alert-dismissible fade" role="alert" id="success-alert-tab5" style="display: none;">
    <span id="success-message-tab5"></span>
    <button type="button" class="close" onclick="hideAlert('success-alert-tab5')">
        <span aria-hidden="true">&times;</span>
    </button>
</div>

<div class="alert alert-danger alert-dismissible fade" role="alert" id="error-alert-tab5" style="display: none;">
    <span id="error-message-tab5"></span>
    <button type="button" class="close" onclick="hideAlert('error-alert-tab5')">
        <span aria-hidden="true">&times;</span>
    </button>
</div>

<div class="d-flex justify-content-between align-items-center mb-3 custom-search-container">
    <div class="d-flex align-items-center">
        <label class="mr-2 mb-0" style="font-size: 0.8rem;">Search:</label>
        <input type="search" class="form-control form-control-sm" id="customSearch5" placeholder="Search orders & disposition..." style="width: 200px;">
    </div>
</div>

<div class="table-container">
    <table class="table table-bordered compact-table sticky-table" id="dataTable5" width="100%" cellspacing="0">
        <thead>
            <tr>
                <th>Inspection ID</th>
                <th>Establishment Name</th>
                <th>Aging 2 Days Finalization</th>
                <th>Status Finalization</th>
                <th>PCT 96 Days</th>
                <th>Date Signed (MIS)</th>
                <th>Status PCT</th>
                <th>Reference Date PCT</th>
                <th>Aging PCT</th>
                <th>Disposition (MIS)</th>
                <th>Disposition (Actual)</th>
                <th>Findings to Comply</th>
                <th>Date of Order (Actual)</th>
                <th>Released Date (Actual)</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @if(isset($ordersAndDisposition) && $ordersAndDisposition->count() > 0)
                @foreach($ordersAndDisposition as $order)
                    <tr data-id="{{ $order->id }}">
                        <td class="editable-cell readonly-cell" data-field="inspection_id">{{ $order->case->inspection_id ?? '-' }}</td>
                        <td class="editable-cell readonly-cell" data-field="establishment_name" title="{{ $order->case->establishment_name ?? '' }}">
                            {{ $order->case ? Str::limit($order->case->establishment_name, 25) : '-' }}
                        </td>
                        <td class="editable-cell" data-field="aging_2_days_finalization">{{ $order->aging_2_days_finalization ?? '-' }}</td>
                        <td class="editable-cell" data-field="status_finalization" data-type="select">
                            @if($order->status_finalization)
                                <span class="badge badge-{{ $order->status_finalization == 'Completed' ? 'success' : ($order->status_finalization == 'Overdue' ? 'danger' : 'warning') }}">
                                    {{ $order->status_finalization }}
                                </span>
                            @else
                                -
                            @endif
                        </td>
                        <td class="editable-cell" data-field="pct_96_days">{{ $order->pct_96_days ?? '-' }}</td>
                        <td class="editable-cell" data-field="date_signed_mis" data-type="date">{{ $order->date_signed_mis ? \Carbon\Carbon::parse($order->date_signed_mis)->format('Y-m-d') : '-' }}</td>
                        <td class="editable-cell" data-field="status_pct" data-type="select">
                            @if($order->status_pct)
                                <span class="badge badge-{{ $order->status_pct == 'Completed' ? 'success' : ($order->status_pct == 'Overdue' ? 'danger' : 'warning') }}">
                                    {{ $order->status_pct }}
                                </span>
                            @else
                                -
                            @endif
                        </td>
                        <td class="editable-cell" data-field="reference_date_pct" data-type="date">{{ $order->reference_date_pct ? \Carbon\Carbon::parse($order->reference_date_pct)->format('Y-m-d') : '-' }}</td>
                        <td class="editable-cell" data-field="aging_pct">{{ $order->aging_pct ?? '-' }}</td>
                        <td class="editable-cell" data-field="disposition_mis">{{ $order->disposition_mis ?? '-' }}</td>
                        <td class="editable-cell" data-field="disposition_actual">{{ $order->disposition_actual ?? '-' }}</td>
                        <td class="editable-cell" data-field="findings_to_comply">{{ $order->findings_to_comply ?? '-' }}</td>
                        <td class="editable-cell" data-field="date_of_order_actual" data-type="date">{{ $order->date_of_order_actual ? \Carbon\Carbon::parse($order->date_of_order_actual)->format('Y-m-d') : '-' }}</td>
                        <td class="editable-cell" data-field="released_date_actual" data-type="date">{{ $order->released_date_actual ? \Carbon\Carbon::parse($order->released_date_actual)->format('Y-m-d') : '-' }}</td>
                        <td>
                            <button class="btn btn-warning btn-sm edit-row-btn-orders" title="Edit Row">
                                <i class="fas fa-edit"></i>
                            </button>
                            <form action="{{ route('orders-and-disposition.destroy', $order->id) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" title="Delete" onclick="return confirm('Are you sure?')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                            
                            @if($order->case && $order->case->current_stage === '5: Orders & Disposition')
                                <form action="{{ route('case.nextStage', $order->case->id) }}" method="POST" style="display:inline;">
                                    @csrf
                                    <button type="submit" class="btn btn-success btn-sm ml-1" title="Move to Compliance & Awards" onclick="return confirm('Complete orders & disposition and move to Compliance & Awards?')">
                                        <i class="fas fa-arrow-right"></i> Next
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="15" class="text-center">No orders & disposition records found.</td>
                </tr>
            @endif
        </tbody>
    </table>
</div>