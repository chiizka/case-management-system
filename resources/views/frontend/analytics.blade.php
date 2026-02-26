@extends('frontend.layouts.app')

@section('content')
<div class="container-fluid">

    {{-- Breadcrumb & Header --}}
    <div class="d-sm-flex align-items-center justify-content-between mb-2">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Labor Standards Cases — January 2026</h1>
            <p class="text-muted small mt-1">
                Process Cycle Time (PCT): <strong>96 days</strong> &nbsp;·&nbsp;
                Type: <strong>Labor Standard Case</strong> &nbsp;·&nbsp;
                <strong>DOLE-5</strong>
            </p>
        </div>
        <button class="btn btn-primary btn-sm shadow-sm" data-toggle="modal" data-target="#generateReportModal">
            <i class="fas fa-file-alt fa-sm text-white-50 mr-1"></i> Generate Report
        </button>
    </div>

    <hr class="mb-4">

    {{-- Stat Cards --}}
    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Cases Handled</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">32</div>
                            <div class="text-xs text-muted mt-1">January 2026</div>
                        </div>
                        <div class="col-auto"><i class="fas fa-folder-open fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Carry-Over Cases</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">24</div>
                            <div class="text-xs text-muted mt-1">From previous year</div>
                        </div>
                        <div class="col-auto"><i class="fas fa-sync-alt fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">New Cases</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">8</div>
                            <div class="text-xs text-muted mt-1">Filed in January</div>
                        </div>
                        <div class="col-auto"><i class="fas fa-plus-circle fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Disposition Rate</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">100%</div>
                            <div class="text-xs text-muted mt-1">New cases disposed</div>
                        </div>
                        <div class="col-auto"><i class="fas fa-check-circle fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- PCT Breakdown + Disposed vs Pending --}}
    <div class="row">
        <div class="col-lg-6 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">PCT Status Breakdown</h6>
                    <span class="badge badge-primary">96-day limit</span>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between small mb-1">
                            <span class="font-weight-bold">Cases Handled</span>
                            <span class="text-muted">Within: 32 &nbsp;·&nbsp; Beyond: 0</span>
                        </div>
                        <div class="progress" style="height:10px;">
                            <div class="progress-bar bg-success" style="width:100%"></div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between small mb-1">
                            <span class="font-weight-bold">Disposed Cases</span>
                            <span class="text-muted">Within: 8 &nbsp;·&nbsp; Beyond: 0</span>
                        </div>
                        <div class="progress" style="height:10px;">
                            <div class="progress-bar bg-success" style="width:100%"></div>
                        </div>
                    </div>
                    <div class="mb-1">
                        <div class="d-flex justify-content-between small mb-1">
                            <span class="font-weight-bold">Pending Cases</span>
                            <span class="text-warning font-weight-bold">24 remaining</span>
                        </div>
                        <div class="progress" style="height:10px;">
                            <div class="progress-bar bg-warning" style="width:75%"></div>
                        </div>
                    </div>
                    <div class="mt-3 small text-muted">
                        <span class="mr-3"><i class="fas fa-circle text-success mr-1"></i> Within PCT</span>
                        <span><i class="fas fa-circle text-danger mr-1"></i> Beyond PCT</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Disposed vs. Pending Cases</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead class="thead-light">
                                <tr>
                                    <th>Indicator</th>
                                    <th class="text-center">Count</th>
                                    <th class="text-center">% of Total</th>
                                    <th class="text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Total Cases Handled</td>
                                    <td class="text-center font-weight-bold">32</td>
                                    <td class="text-center">100%</td>
                                    <td class="text-center"><span class="badge badge-primary">Base</span></td>
                                </tr>
                                <tr>
                                    <td>Disposed Cases</td>
                                    <td class="text-center font-weight-bold">8</td>
                                    <td class="text-center">25%</td>
                                    <td class="text-center"><span class="badge badge-success">Within PCT</span></td>
                                </tr>
                                <tr>
                                    <td>Pending Cases</td>
                                    <td class="text-center font-weight-bold">24</td>
                                    <td class="text-center">75%</td>
                                    <td class="text-center"><span class="badge badge-warning">Monitoring</span></td>
                                </tr>
                                <tr>
                                    <td>Net Cases (New)</td>
                                    <td class="text-center font-weight-bold">8</td>
                                    <td class="text-center">25%</td>
                                    <td class="text-center"><span class="badge badge-info">This Month</span></td>
                                </tr>
                                <tr class="table-success">
                                    <td><strong>Disposition Rate</strong></td>
                                    <td class="text-center font-weight-bold">100%</td>
                                    <td class="text-center">—</td>
                                    <td class="text-center"><span class="badge badge-success">✓ Target Met</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Monthly Breakdown Table --}}
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 d-flex align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Monthly Case Breakdown</h6>
                    <span class="badge badge-secondary">Form No. 1</span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover table-sm" id="analyticsTable" width="100%">
                            <thead class="thead-light">
                                <tr>
                                    <th>Indicator</th>
                                    <th class="text-center">Total</th>
                                    <th class="text-center">Jan</th>
                                    <th class="text-center">Feb</th>
                                    <th class="text-center">Mar</th>
                                    <th class="text-center">Apr</th>
                                    <th class="text-center">May</th>
                                    <th class="text-center">Jun</th>
                                    <th class="text-center">Jul–Dec</th>
                                    <th class="text-center">PCT Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Carry-Over Cases</td>
                                    <td class="text-center">24</td><td class="text-center">24</td>
                                    <td class="text-center text-muted">—</td><td class="text-center text-muted">—</td>
                                    <td class="text-center text-muted">—</td><td class="text-center text-muted">—</td>
                                    <td class="text-center text-muted">—</td><td class="text-center text-muted">—</td>
                                    <td class="text-center"><span class="badge badge-primary">Ongoing</span></td>
                                </tr>
                                <tr>
                                    <td>New Cases</td>
                                    <td class="text-center">8</td><td class="text-center">8</td>
                                    <td class="text-center">0</td><td class="text-center">0</td>
                                    <td class="text-center">0</td><td class="text-center">0</td>
                                    <td class="text-center">0</td><td class="text-center">0</td>
                                    <td class="text-center"><span class="badge badge-success">Within PCT</span></td>
                                </tr>
                                <tr>
                                    <td>Cases Handled (Within PCT)</td>
                                    <td class="text-center">32</td><td class="text-center">32</td>
                                    <td class="text-center">0</td><td class="text-center">0</td>
                                    <td class="text-center">0</td><td class="text-center">0</td>
                                    <td class="text-center">0</td><td class="text-center">0</td>
                                    <td class="text-center"><span class="badge badge-success">✓ Within PCT</span></td>
                                </tr>
                                <tr>
                                    <td>Cases Handled (Beyond PCT)</td>
                                    <td class="text-center">0</td><td class="text-center">0</td>
                                    <td class="text-center">0</td><td class="text-center">0</td>
                                    <td class="text-center">0</td><td class="text-center">0</td>
                                    <td class="text-center">0</td><td class="text-center">0</td>
                                    <td class="text-center"><span class="badge badge-success">None</span></td>
                                </tr>
                                <tr>
                                    <td>Disposed Cases</td>
                                    <td class="text-center">8</td><td class="text-center">8</td>
                                    <td class="text-center">0</td><td class="text-center">0</td>
                                    <td class="text-center">0</td><td class="text-center">0</td>
                                    <td class="text-center">0</td><td class="text-center">0</td>
                                    <td class="text-center"><span class="badge badge-success">Within PCT</span></td>
                                </tr>
                                <tr>
                                    <td>Pending Cases</td>
                                    <td class="text-center text-warning font-weight-bold">24</td>
                                    <td class="text-center">24</td>
                                    <td class="text-center">0</td><td class="text-center">0</td>
                                    <td class="text-center">0</td><td class="text-center">0</td>
                                    <td class="text-center">0</td><td class="text-center">0</td>
                                    <td class="text-center"><span class="badge badge-warning">Monitoring</span></td>
                                </tr>
                                <tr class="table-success">
                                    <td><strong>Disposition Rate</strong></td>
                                    <td class="text-center text-success font-weight-bold">100%</td>
                                    <td class="text-center">100%</td>
                                    <td class="text-center text-muted">—</td><td class="text-center text-muted">—</td>
                                    <td class="text-center text-muted">—</td><td class="text-center text-muted">—</td>
                                    <td class="text-center text-muted">—</td><td class="text-center text-muted">—</td>
                                    <td class="text-center"><span class="badge badge-success">✓ Target Met</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

{{-- Generate Report Modal --}}
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
                        <button type="button" class="btn btn-primary active" data-form="1">
                            Form No. 1
                        </button>
                        <button type="button" class="btn btn-outline-primary" data-form="3">
                            Form No. 3
                        </button>
                    </div>
                    <small class="text-muted mt-1 d-block" id="formDescription">
                        Cases handled by month
                    </small>
                </div>

                <div class="form-group">
                    <label class="font-weight-bold small text-uppercase text-muted mb-1">Year</label>
                    <select id="reportYear" class="form-control form-control-sm" required>
                        @for ($y = now()->year; $y >= 2020; $y--)
                            <option value="{{ $y }}" {{ $y == now()->year ? 'selected' : '' }}>
                                {{ $y }}
                            </option>
                        @endfor
                    </select>
                </div>

                <div class="form-group mb-0">
                    <label class="font-weight-bold small text-uppercase text-muted mb-1">Month</label>
                    <select id="reportMonth" class="form-control form-control-sm" required>
                        @foreach([1=>'January',2=>'February',3=>'March',4=>'April',
                                  5=>'May',6=>'June',7=>'July',8=>'August',
                                  9=>'September',10=>'October',11=>'November',12=>'December']
                                 as $num => $name)
                            <option value="{{ $num }}" {{ $num == now()->month ? 'selected' : '' }}>
                                {{ $name }}
                            </option>
                        @endforeach
                    </select>
                </div>

            </div>

            <div class="modal-footer py-2">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success btn-sm" id="generateBtn"
                        onclick="submitReport()">
                    <i class="fas fa-file-excel mr-1"></i> Download
                </button>
            </div>

        </div>
    </div>
</div>

{{-- Hidden forms — one per report type --}}
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

<script>
    var selectedForm = '1';

    // Toggle active form button
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
        var year  = document.getElementById('reportYear').value;
        var month = document.getElementById('reportMonth').value;
        var btn   = document.getElementById('generateBtn');

        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Generating…';

        if (selectedForm === '3') {
            document.getElementById('f3year').value  = year;
            document.getElementById('f3month').value = month;
            document.getElementById('form3Action').submit();
        } else {
            document.getElementById('f1year').value  = year;
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

@endsection