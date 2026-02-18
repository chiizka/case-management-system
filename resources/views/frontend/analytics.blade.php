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
                        <div class="col-auto">
                            <i class="fas fa-folder-open fa-2x text-gray-300"></i>
                        </div>
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
                        <div class="col-auto">
                            <i class="fas fa-sync-alt fa-2x text-gray-300"></i>
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
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">New Cases</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">8</div>
                            <div class="text-xs text-muted mt-1">Filed in January</div>
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
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Disposition Rate</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">100%</div>
                            <div class="text-xs text-muted mt-1">New cases disposed</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- PCT Breakdown + Disposed vs Pending --}}
    <div class="row">

        {{-- PCT Status --}}
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
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar bg-success" style="width: 100%"></div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between small mb-1">
                            <span class="font-weight-bold">Disposed Cases</span>
                            <span class="text-muted">Within: 8 &nbsp;·&nbsp; Beyond: 0</span>
                        </div>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar bg-success" style="width: 100%"></div>
                        </div>
                    </div>

                    <div class="mb-1">
                        <div class="d-flex justify-content-between small mb-1">
                            <span class="font-weight-bold">Pending Cases</span>
                            <span class="text-warning font-weight-bold">24 remaining</span>
                        </div>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar bg-warning" style="width: 75%"></div>
                        </div>
                    </div>

                    <div class="mt-3 small text-muted">
                        <span class="mr-3"><i class="fas fa-circle text-success mr-1"></i> Within PCT</span>
                        <span><i class="fas fa-circle text-danger mr-1"></i> Beyond PCT</span>
                    </div>

                </div>
            </div>
        </div>

        {{-- Disposed vs Pending --}}
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
                                    <td class="text-center">24</td>
                                    <td class="text-center">24</td>
                                    <td class="text-center text-muted">—</td>
                                    <td class="text-center text-muted">—</td>
                                    <td class="text-center text-muted">—</td>
                                    <td class="text-center text-muted">—</td>
                                    <td class="text-center text-muted">—</td>
                                    <td class="text-center text-muted">—</td>
                                    <td class="text-center"><span class="badge badge-primary">Ongoing</span></td>
                                </tr>
                                <tr>
                                    <td>New Cases</td>
                                    <td class="text-center">8</td>
                                    <td class="text-center">8</td>
                                    <td class="text-center">0</td>
                                    <td class="text-center">0</td>
                                    <td class="text-center">0</td>
                                    <td class="text-center">0</td>
                                    <td class="text-center">0</td>
                                    <td class="text-center">0</td>
                                    <td class="text-center"><span class="badge badge-success">Within PCT</span></td>
                                </tr>
                                <tr>
                                    <td>Cases Handled (Within PCT)</td>
                                    <td class="text-center">32</td>
                                    <td class="text-center">32</td>
                                    <td class="text-center">0</td>
                                    <td class="text-center">0</td>
                                    <td class="text-center">0</td>
                                    <td class="text-center">0</td>
                                    <td class="text-center">0</td>
                                    <td class="text-center">0</td>
                                    <td class="text-center"><span class="badge badge-success">✓ Within PCT</span></td>
                                </tr>
                                <tr>
                                    <td>Cases Handled (Beyond PCT)</td>
                                    <td class="text-center">0</td>
                                    <td class="text-center">0</td>
                                    <td class="text-center">0</td>
                                    <td class="text-center">0</td>
                                    <td class="text-center">0</td>
                                    <td class="text-center">0</td>
                                    <td class="text-center">0</td>
                                    <td class="text-center">0</td>
                                    <td class="text-center"><span class="badge badge-success">None</span></td>
                                </tr>
                                <tr>
                                    <td>Disposed Cases</td>
                                    <td class="text-center">8</td>
                                    <td class="text-center">8</td>
                                    <td class="text-center">0</td>
                                    <td class="text-center">0</td>
                                    <td class="text-center">0</td>
                                    <td class="text-center">0</td>
                                    <td class="text-center">0</td>
                                    <td class="text-center">0</td>
                                    <td class="text-center"><span class="badge badge-success">Within PCT</span></td>
                                </tr>
                                <tr>
                                    <td>Pending Cases</td>
                                    <td class="text-center text-warning font-weight-bold">24</td>
                                    <td class="text-center">24</td>
                                    <td class="text-center">0</td>
                                    <td class="text-center">0</td>
                                    <td class="text-center">0</td>
                                    <td class="text-center">0</td>
                                    <td class="text-center">0</td>
                                    <td class="text-center">0</td>
                                    <td class="text-center"><span class="badge badge-warning">Monitoring</span></td>
                                </tr>
                                <tr class="table-success">
                                    <td><strong>Disposition Rate</strong></td>
                                    <td class="text-center text-success font-weight-bold">100%</td>
                                    <td class="text-center">100%</td>
                                    <td class="text-center text-muted">—</td>
                                    <td class="text-center text-muted">—</td>
                                    <td class="text-center text-muted">—</td>
                                    <td class="text-center text-muted">—</td>
                                    <td class="text-center text-muted">—</td>
                                    <td class="text-center text-muted">—</td>
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
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-file-alt mr-2"></i> Generate Analytics Report
                </h5>
                <button class="close text-white" type="button" data-dismiss="modal">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">

                {{-- Quick Stats inside modal --}}
                <div class="row text-center mb-4">
                    <div class="col-3">
                        <div class="h4 font-weight-bold text-primary mb-0">32</div>
                        <div class="small text-muted">Total Cases</div>
                    </div>
                    <div class="col-3">
                        <div class="h4 font-weight-bold text-success mb-0">100%</div>
                        <div class="small text-muted">Within PCT</div>
                    </div>
                    <div class="col-3">
                        <div class="h4 font-weight-bold text-danger mb-0">0</div>
                        <div class="small text-muted">Beyond PCT</div>
                    </div>
                    <div class="col-3">
                        <div class="h4 font-weight-bold text-warning mb-0">24</div>
                        <div class="small text-muted">Pending</div>
                    </div>
                </div>

                <hr>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="font-weight-bold small">Report Form</label>
                            <select class="form-control form-control-sm">
                                <option>Form No. 1 – Cases Handled</option>
                                <option>Form No. 3 – Execution & Compliance</option>
                                <option>Combined Report (Form 1 + 3)</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="font-weight-bold small">Office / Agency</label>
                            <select class="form-control form-control-sm">
                                <option>DOLE Region V (All)</option>
                                <option>CNFO</option>
                                <option>CSFO</option>
                                <option>MFO</option>
                                <option>RO5</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="font-weight-bold small">Year</label>
                            <select class="form-control form-control-sm">
                                <option>2026</option>
                                <option>2025</option>
                                <option>2024</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="font-weight-bold small">Month Range</label>
                            <select class="form-control form-control-sm">
                                <option>January only</option>
                                <option>January – March (Q1)</option>
                                <option>January – June (H1)</option>
                                <option>Full Year</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="font-weight-bold small">Include Sections</label>
                    <div>
                        <div class="custom-control custom-checkbox custom-control-inline">
                            <input type="checkbox" class="custom-control-input" id="chk1" checked>
                            <label class="custom-control-label" for="chk1">Case Volume</label>
                        </div>
                        <div class="custom-control custom-checkbox custom-control-inline">
                            <input type="checkbox" class="custom-control-input" id="chk2" checked>
                            <label class="custom-control-label" for="chk2">PCT Status</label>
                        </div>
                        <div class="custom-control custom-checkbox custom-control-inline">
                            <input type="checkbox" class="custom-control-input" id="chk3" checked>
                            <label class="custom-control-label" for="chk3">Disposition Rate</label>
                        </div>
                        <div class="custom-control custom-checkbox custom-control-inline">
                            <input type="checkbox" class="custom-control-input" id="chk4" checked>
                            <label class="custom-control-label" for="chk4">Pending Cases</label>
                        </div>
                        <div class="custom-control custom-checkbox custom-control-inline">
                            <input type="checkbox" class="custom-control-input" id="chk5">
                            <label class="custom-control-label" for="chk5">Monetary Awards</label>
                        </div>
                        <div class="custom-control custom-checkbox custom-control-inline">
                            <input type="checkbox" class="custom-control-input" id="chk6">
                            <label class="custom-control-label" for="chk6">Workers Benefitted</label>
                        </div>
                    </div>
                </div>

                <div class="form-group mb-0">
                    <label class="font-weight-bold small">Output Format</label>
                    <div class="btn-group btn-group-sm d-block" role="group">
                        <button type="button" class="btn btn-primary">XLSX</button>
                        <button type="button" class="btn btn-outline-primary">PDF</button>
                        <button type="button" class="btn btn-outline-primary">CSV</button>
                    </div>
                </div>

            </div>
            <div class="modal-footer">
                <small class="text-muted mr-auto">Est. generation time: ~3 sec</small>
                <button class="btn btn-secondary btn-sm" type="button" data-dismiss="modal">Cancel</button>
                <button class="btn btn-primary btn-sm" type="button">
                    <i class="fas fa-file-download mr-1"></i> Generate Report
                </button>
            </div>
        </div>
    </div>
</div>

@endsection