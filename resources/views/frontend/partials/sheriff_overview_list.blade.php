@if(empty($cases))
    <div class="text-center text-muted py-5">
        <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
        <p class="mb-0">No active cases currently with {{ $roleLabel }}.</p>
    </div>
@else
    @foreach($cases as $case)
    <div class="d-flex justify-content-between align-items-start py-2" style="border-bottom:1px solid #f0f0f0;">
        <div>
            <div class="font-weight-bold text-dark" style="font-size:0.85rem;">{{ $case['case_no'] }}</div>
            <div class="text-muted" style="font-size:0.75rem;">{{ $case['establishment'] }}</div>
            <div style="font-size:0.72rem;" class="mt-1">
                @if($case['sheriff_designate'])
                    <span class="badge badge-light border">
                        <i class="fas fa-user-shield mr-1 text-secondary"></i>{{ $case['sheriff_designate'] }}
                    </span>
                @else
                    <span class="text-muted font-italic">No sheriff designate assigned</span>
                @endif
            </div>
            @if($case['missing_last_month'] && $case['total_reports'] > 0)
            <span class="badge badge-danger mt-1" style="font-size:0.68rem;">
                <i class="fas fa-exclamation-circle mr-1"></i>Missing {{ $case['missing_month_label'] }} report
            </span>
            @endif
        </div>
        <div class="text-right" style="flex-shrink:0;">
            @if($case['total_reports'] > 0)
                <button type="button" class="btn btn-sm btn-outline-primary view-reports-grid-btn"
                        data-case-id="{{ $case['case_id'] }}"
                        data-malsu-id="{{ $case['malsu_id'] }}"
                        data-case-no="{{ $case['case_no'] }}"
                        data-establishment="{{ $case['establishment'] }}">
                    <i class="fas fa-table"></i> {{ $case['total_reports'] }} report{{ $case['total_reports'] > 1 ? 's' : '' }}
                </button>
                <div class="text-muted mt-1" style="font-size:0.68rem;">
                    Last: {{ $case['latest_month_label'] }} ({{ $case['latest_submitted_at'] }})
                </div>
            @else
                <span class="badge badge-light border" style="font-size:0.7rem;">No reports filed yet</span>
            @endif
        </div>
    </div>
    @endforeach
@endif