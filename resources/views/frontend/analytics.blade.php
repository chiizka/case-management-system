@extends('frontend.layouts.app')

@section('content')

@php
    $monthNames = [
        1=>'January',2=>'February',3=>'March',4=>'April',
        5=>'May',6=>'June',7=>'July',8=>'August',
        9=>'September',10=>'October',11=>'November',12=>'December'
    ];
    $shortMonths = [
        1=>'Jan',2=>'Feb',3=>'Mar',4=>'Apr',5=>'May',6=>'Jun',
        7=>'Jul',8=>'Aug',9=>'Sep',10=>'Oct',11=>'Nov',12=>'Dec'
    ];
    $currentMonthName = $monthNames[$month];
    $disposedPct  = $totalHandled > 0 ? round(($disposedThisMonth / $totalHandled) * 100) : 0;
    $pendingPct   = $totalHandled > 0 ? round(($pending / $totalHandled) * 100) : 0;
@endphp

<style>
    .analytics-filter-bar {
        background: #f8f9fc;
        border: 1px solid #e3e6f0;
        border-radius: .5rem;
        padding: .75rem 1rem;
        margin-bottom: 1.5rem;
    }
    .province-bar { height: 8px; border-radius: 4px; background: #e3e6f0; overflow: hidden; margin-top: 4px; }
    .province-bar-fill { height: 100%; border-radius: 4px; transition: width .6s ease; }
    .stage-pill { font-size: .7rem; padding: .2rem .55rem; border-radius: 999px; font-weight: 600; }
    .metric-divider { border-left: 3px solid #e3e6f0; padding-left: 1rem; }
    .activity-row:hover { background: #f8f9fc; }
    .pct-ring { position: relative; width: 80px; height: 80px; flex-shrink: 0; }
    .pct-ring svg { transform: rotate(-90deg); }
    .pct-ring-label {
        position: absolute; inset: 0;
        display: flex; flex-direction: column;
        align-items: center; justify-content: center;
    }
    .kpi-sub { font-size: .72rem; margin-top: 2px; }
</style>

<div class="container-fluid">

    {{-- ── Header ──────────────────────────────────────────────────────── --}}
    <div class="d-sm-flex align-items-start justify-content-between mb-3">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Analytics Dashboard</h1>
            <p class="text-muted small mt-1 mb-0">
                Labor Standards Cases &nbsp;·&nbsp;
                <strong>{{ $currentMonthName }} {{ $year }}</strong> &nbsp;·&nbsp;
                PCT: <strong>96 days</strong> &nbsp;·&nbsp;
                <strong>DOLE Region V</strong>
            </p>
        </div>
        <button class="btn btn-primary btn-sm shadow-sm mt-2 mt-sm-0"
                data-toggle="modal" data-target="#generateReportModal">
            <i class="fas fa-file-excel fa-sm mr-1"></i> Generate Report
        </button>
    </div>

    {{-- ── Filter Bar ───────────────────────────────────────────────────── --}}
    <div class="analytics-filter-bar d-flex align-items-center flex-wrap">
        <span class="small font-weight-bold text-muted mr-3">
            <i class="fas fa-filter mr-1"></i> Filter by:
        </span>
        <form method="GET" action="{{ route('analytics.index') }}"
              class="d-flex align-items-center" style="gap:.5rem; flex-wrap:wrap;">
            <select name="year" class="form-control form-control-sm" style="width:90px;"
                    onchange="this.form.submit()">
                @for($y = now()->year; $y >= 2020; $y--)
                    <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
            </select>
            <select name="month" class="form-control form-control-sm" style="width:130px;"
                    onchange="this.form.submit()">
                @foreach($monthNames as $num => $name)
                    <option value="{{ $num }}" {{ $num == $month ? 'selected' : '' }}>{{ $name }}</option>
                @endforeach
            </select>
            <span class="small text-muted">
                Data as of end of {{ $currentMonthName }} {{ $year }}
            </span>
        </form>
    </div>

    {{-- ── KPI Cards ────────────────────────────────────────────────────── --}}
    <div class="row mb-4">

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Cases Handled</div>
                            <div class="h4 mb-0 font-weight-bold text-gray-800">{{ number_format($totalHandled) }}</div>
                            <div class="kpi-sub text-muted">
                                {{ number_format($carryOver) }} carry-over
                                + {{ number_format($newCases) }} new this month
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-folder-open fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">New Cases This Month</div>
                            <div class="h4 mb-0 font-weight-bold text-gray-800">{{ number_format($newCases) }}</div>
                            <div class="kpi-sub text-muted">Filed in {{ $currentMonthName }} {{ $year }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-plus-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Disposed This Month</div>
                            <div class="h4 mb-0 font-weight-bold text-gray-800">{{ number_format($disposedThisMonth) }}</div>
                            <div class="kpi-sub">
                                @if($disposedWithin > 0 || $disposedBeyond > 0)
                                    <span class="text-success">{{ $disposedWithin }} within</span>
                                    @if($disposedBeyond > 0)
                                        &nbsp;<span class="text-danger">{{ $disposedBeyond }} beyond PCT</span>
                                    @endif
                                @else
                                    <span class="text-muted">None recorded</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-double fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            @php $rateColor = $dispositionRate >= 100 ? 'success' : ($dispositionRate >= 75 ? 'warning' : 'danger'); @endphp
            <div class="card border-left-{{ $rateColor }} shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-{{ $rateColor }} text-uppercase mb-1">Disposition Rate</div>
                            <div class="h4 mb-0 font-weight-bold text-gray-800">{{ $dispositionRate }}%</div>
                            <div class="kpi-sub">
                                @if($dispositionRate >= 100)
                                    <span class="text-success"><i class="fas fa-check-circle mr-1"></i>Target met</span>
                                @elseif($dispositionRate >= 75)
                                    <span class="text-warning"><i class="fas fa-exclamation-circle mr-1"></i>Near target</span>
                                @else
                                    <span class="text-danger"><i class="fas fa-times-circle mr-1"></i>Below target</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chart-pie fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- ── Row 2: PCT Status Ring + Province Table ──────────────────────── --}}
    <div class="row mb-4">

        {{-- PCT Status --}}
        <div class="col-lg-5 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3 d-flex align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">PCT Status — {{ $currentMonthName }}</h6>
                    <span class="badge badge-primary badge-pill">96-day limit</span>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-4">
                        {{-- Disposition ring --}}
                        <div class="pct-ring mr-3">
                            @php $circumference = 201.06; $filled = round(($dispositionRate/100)*$circumference); @endphp
                            <svg width="80" height="80" viewBox="0 0 80 80">
                                <circle cx="40" cy="40" r="32" fill="none" stroke="#e3e6f0" stroke-width="8"/>
                                <circle cx="40" cy="40" r="32" fill="none"
                                    stroke="{{ $dispositionRate >= 100 ? '#1cc88a' : ($dispositionRate >= 75 ? '#f6c23e' : '#e74a3b') }}"
                                    stroke-width="8"
                                    stroke-dasharray="{{ min($filled, $circumference) }} {{ $circumference }}"
                                    stroke-linecap="round"/>
                            </svg>
                            <div class="pct-ring-label">
                                <span class="font-weight-bold text-gray-800" style="font-size:.85rem;">{{ $dispositionRate }}%</span>
                                <span class="text-muted" style="font-size:.58rem; line-height:1;">disposed</span>
                            </div>
                        </div>
                        {{-- Stats --}}
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between small mb-1">
                                <span class="text-muted">Total Handled</span>
                                <span class="font-weight-bold">{{ $totalHandled }}</span>
                            </div>
                            <div class="d-flex justify-content-between small mb-1">
                                <span><i class="fas fa-circle text-info mr-1" style="font-size:.5rem;"></i>Disposed</span>
                                <span>{{ $disposedThisMonth }}</span>
                            </div>
                            <div class="d-flex justify-content-between small mb-1">
                                <span><i class="fas fa-circle text-success mr-1" style="font-size:.5rem;"></i>Within PCT</span>
                                <span class="text-success font-weight-bold">{{ $disposedWithin }}</span>
                            </div>
                            <div class="d-flex justify-content-between small mb-1">
                                <span><i class="fas fa-circle text-danger mr-1" style="font-size:.5rem;"></i>Beyond PCT</span>
                                <span class="{{ $disposedBeyond > 0 ? 'text-danger font-weight-bold' : 'text-muted' }}">{{ $disposedBeyond }}</span>
                            </div>
                            <div class="d-flex justify-content-between small">
                                <span><i class="fas fa-circle text-warning mr-1" style="font-size:.5rem;"></i>Pending</span>
                                <span class="text-warning font-weight-bold">{{ $pending }}</span>
                            </div>
                        </div>
                    </div>

                    <hr class="my-3">

                    {{-- Monetary & Workers --}}
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="text-xs text-muted text-uppercase mb-1">Monetary Award</div>
                            <div class="h6 font-weight-bold text-gray-800 mb-0">₱{{ number_format($monetary, 2) }}</div>
                            <div class="text-xs text-muted">{{ $currentMonthName }}</div>
                        </div>
                        <div class="col-6 metric-divider">
                            <div class="text-xs text-muted text-uppercase mb-1">Workers Benefitted</div>
                            <div class="h6 font-weight-bold text-gray-800 mb-0">{{ number_format($workers) }}</div>
                            <div class="text-xs text-muted">{{ $currentMonthName }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Province Breakdown --}}
        <div class="col-lg-7 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3 d-flex align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Cases by Province</h6>
                    <span class="badge badge-secondary badge-pill">Active caseload</span>
                </div>
                <div class="card-body">
                    @php $maxProvince = $byProvince->max('total') ?: 1; @endphp
                    <table class="table table-sm mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>Province</th>
                                <th class="text-center" style="width:65px;">Active</th>
                                <th class="text-center" style="width:75px;">
                                    Disposed<br><small class="font-weight-normal text-muted">this month</small>
                                </th>
                                <th>Caseload</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($byProvince as $prov)
                            @php
                                $barPct   = round(($prov['total'] / $maxProvince) * 100);
                                $barColor = $prov['active'] > 10 ? '#e74a3b' : ($prov['active'] > 5 ? '#f6c23e' : '#1cc88a');
                                $badge    = $prov['active'] > 10 ? 'danger' : ($prov['active'] > 5 ? 'warning' : 'success');
                            @endphp
                            <tr>
                                <td class="font-weight-bold small align-middle">{{ $prov['name'] }}</td>
                                <td class="text-center align-middle">
                                    <span class="badge badge-{{ $badge }}">{{ $prov['active'] }}</span>
                                </td>
                                <td class="text-center small align-middle text-muted">
                                    {{ $prov['disposed'] > 0 ? $prov['disposed'] : '—' }}
                                </td>
                                <td class="align-middle">
                                    <div class="province-bar">
                                        <div class="province-bar-fill" style="width:{{ $barPct }}%; background:{{ $barColor }};"></div>
                                    </div>
                                    <div class="text-xs text-muted mt-1">{{ $prov['total'] }} total cases</div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    {{-- ── Row 3: Monthly Trend Chart + Stage Distribution ─────────────── --}}
    <div class="row mb-4">

        <div class="col-lg-8 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3 d-flex align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Monthly Trend — {{ $year }}</h6>
                    <div class="small">
                        <span class="mr-3">
                            <i class="fas fa-square text-primary mr-1"></i>New
                        </span>
                        <span>
                            <i class="fas fa-square text-success mr-1"></i>Disposed
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="trendChart" height="130"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-4 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Active Cases by Stage</h6>
                </div>
                <div class="card-body p-0">
                    @php
                        $stageMap = [
                            1 => ['label'=>'Inspection',        'color'=>'primary'],
                            2 => ['label'=>'Docketing',         'color'=>'info'],
                            3 => ['label'=>'Hearing',           'color'=>'warning'],
                            4 => ['label'=>'Review & Drafting', 'color'=>'secondary'],
                            5 => ['label'=>'Orders',            'color'=>'danger'],
                            6 => ['label'=>'Compliance',        'color'=>'success'],
                            7 => ['label'=>'Appeals',           'color'=>'dark'],
                        ];
                        $stageTotal = $stageDistribution->sum();
                    @endphp
                    <div class="list-group list-group-flush">
                        @forelse($stageDistribution as $stage => $count)
                        @php
                            $info = $stageMap[$stage] ?? ['label'=>'Stage '.$stage,'color'=>'secondary'];
                            $pct  = $stageTotal > 0 ? round(($count/$stageTotal)*100) : 0;
                        @endphp
                        <div class="list-group-item px-3 py-2 activity-row">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="badge badge-{{ $info['color'] }} stage-pill">{{ $info['label'] }}</span>
                                <span class="font-weight-bold small">{{ $count }}</span>
                            </div>
                            <div class="progress" style="height:4px;">
                                <div class="progress-bar bg-{{ $info['color'] }}" style="width:{{ $pct }}%"></div>
                            </div>
                        </div>
                        @empty
                        <div class="list-group-item text-center text-muted py-4">
                            <i class="fas fa-inbox fa-2x d-block mb-2"></i>
                            No active cases
                        </div>
                        @endforelse
                    </div>
                    @if($stageTotal > 0)
                    <div class="px-3 py-2 border-top small text-muted">
                        Total active: <strong class="text-gray-800">{{ $stageTotal }}</strong>
                    </div>
                    @endif
                </div>
            </div>
        </div>

    </div>

    {{-- ── Row 4: Recently Disposed Cases ──────────────────────────────── --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3 d-flex align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Recently Disposed Cases</h6>
                    <a href="{{ route('archive.index') }}" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-archive mr-1"></i> View All
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th class="pl-3">Case / Inspection No.</th>
                                    <th>Establishment</th>
                                    <th>Province</th>
                                    <th>Status</th>
                                    <th>Date Archived</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentActivity as $case)
                                @php
                                    $sColor = match($case->overall_status) {
                                        'Completed' => 'success',
                                        'Appealed'  => 'warning',
                                        'Disposed'  => 'info',
                                        default     => 'secondary',
                                    };
                                @endphp
                                <tr class="activity-row">
                                    <td class="pl-3 align-middle font-weight-bold small">
                                        {{ $case->case_no ?? $case->inspection_id ?? 'N/A' }}
                                    </td>
                                    <td class="small align-middle">
                                        {{ Str::limit($case->establishment_name ?? '—', 40) }}
                                    </td>
                                    <td class="small align-middle">{{ $case->po_office ?? '—' }}</td>
                                    <td class="align-middle">
                                        <span class="badge badge-{{ $sColor }}">{{ $case->overall_status }}</span>
                                    </td>
                                    <td class="small text-muted align-middle">
                                        {{ $case->updated_at->format('M d, Y') }}
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">
                                        <i class="fas fa-inbox fa-2x d-block mb-2"></i>
                                        No disposed cases yet
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

{{-- ── Generate Report Modal ────────────────────────────────────────────── --}}
<div class="modal fade" id="generateReportModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white py-3">
                <h5 class="modal-title font-weight-bold">
                    <i class="fas fa-file-excel mr-2"></i> Generate Report
                </h5>
                <button class="close text-white" type="button" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label class="font-weight-bold small text-uppercase text-muted mb-1">Form</label>
                    <div class="btn-group btn-group-sm w-100" id="formToggle">
                        <button type="button" class="btn btn-primary active" data-form="1">Form No. 1</button>
                        <button type="button" class="btn btn-outline-primary" data-form="3">Form No. 3</button>
                    </div>
                    <small class="text-muted mt-1 d-block" id="formDescription">Cases handled by month</small>
                </div>
                <div class="form-group">
                    <label class="font-weight-bold small text-uppercase text-muted mb-1">Year</label>
                    <select id="reportYear" class="form-control form-control-sm">
                        @for($y = now()->year; $y >= 2020; $y--)
                            <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
                <div class="form-group mb-0">
                    <label class="font-weight-bold small text-uppercase text-muted mb-1">Month</label>
                    <select id="reportMonth" class="form-control form-control-sm">
                        @foreach($monthNames as $num => $name)
                            <option value="{{ $num }}" {{ $num == $month ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success btn-sm" id="generateBtn" onclick="submitReport()">
                    <i class="fas fa-file-excel mr-1"></i> Download
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Hidden report forms --}}
<form id="form1Action" method="POST" action="{{ route('reports.form1.generate') }}" style="display:none">
    @csrf
    <input type="hidden" name="office" value="">
    <input type="hidden" name="year"  id="f1year">
    <input type="hidden" name="month" id="f1month">
</form>
<form id="form3Action" method="POST" action="{{ route('reports.form3.generate') }}" style="display:none">
    @csrf
    <input type="hidden" name="year"  id="f3year">
    <input type="hidden" name="month" id="f3month">
</form>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
// ── Monthly Trend Chart ──────────────────────────────────────────────────────
(function() {
    const labels      = @json(array_values(array_map(fn($m) => $shortMonths[$m], array_keys($monthlyTrend))));
    const newData     = @json(array_values(array_column($monthlyTrend, 'new')));
    const disposedData = @json(array_values(array_column($monthlyTrend, 'disposed')));

    new Chart(document.getElementById('trendChart'), {
        type: 'bar',
        data: {
            labels,
            datasets: [
                {
                    label: 'New Cases',
                    data: newData,
                    backgroundColor: 'rgba(78,115,223,.75)',
                    borderColor: 'rgba(78,115,223,1)',
                    borderWidth: 1,
                    borderRadius: 3,
                },
                {
                    label: 'Disposed',
                    data: disposedData,
                    backgroundColor: 'rgba(28,200,138,.75)',
                    borderColor: 'rgba(28,200,138,1)',
                    borderWidth: 1,
                    borderRadius: 3,
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        afterBody: function(items) {
                            const i = items[0].dataIndex;
                            const n = newData[i], d = disposedData[i];
                            return n > 0 ? `Disposition rate: ${Math.round((d/n)*100)}%` : '';
                        }
                    }
                }
            },
            scales: {
                x: { grid: { display: false } },
                y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#f0f0f0' } }
            }
        }
    });
})();

// ── Report Modal ─────────────────────────────────────────────────────────────
var selectedForm = '1';
document.querySelectorAll('#formToggle .btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        document.querySelectorAll('#formToggle .btn').forEach(function(b) {
            b.classList.remove('btn-primary', 'active');
            b.classList.add('btn-outline-primary');
        });
        this.classList.remove('btn-outline-primary');
        this.classList.add('btn-primary', 'active');
        selectedForm = this.dataset.form;
        document.getElementById('formDescription').textContent =
            selectedForm === '1'
                ? 'Cases handled by month'
                : 'Execution & satisfaction of decisions/orders';
    });
});

function submitReport() {
    var year = document.getElementById('reportYear').value;
    var month = document.getElementById('reportMonth').value;
    var btn = document.getElementById('generateBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Generating…';
    if (selectedForm === '3') {
        document.getElementById('f3year').value = year;
        document.getElementById('f3month').value = month;
        document.getElementById('form3Action').submit();
    } else {
        document.getElementById('f1year').value = year;
        document.getElementById('f1month').value = month;
        document.getElementById('form1Action').submit();
    }
    setTimeout(function() {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-file-excel mr-1"></i> Download';
        $('#generateReportModal').modal('hide');
    }, 5000);
}
</script>
@endpush

@endsection