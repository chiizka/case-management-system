@extends('frontend.layouts.app')
@section('content')

<div id="content">
    <!-- Begin Page Content -->
    <div class="container-fluid">

        <!-- Page Heading -->
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
            <a href="#" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
                <i class="fas fa-download fa-sm text-white-50"></i> Generate Report
            </a>
        </div>

        <!-- Content Row - Statistics Cards -->
        <div class="row">

            <!-- Total Cases Card -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    Total Cases</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalCases }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-folder fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Active Cases Card -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                    Active Cases</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $activeCases }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pending Inspections Card -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-info shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                    Pending Inspections</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $pendingInspections }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-search fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Closed Cases Card -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-warning shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                    Closed Cases</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $closedCases }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Content Row - Charts -->
        <div class="row">

            <!-- Cases Trend Chart -->
            <div class="col-xl-8 col-lg-7">
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">Cases Overview (Last 6 Months)</h6>
                        <div class="dropdown no-arrow">
                            <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in"
                                aria-labelledby="dropdownMenuLink">
                                <div class="dropdown-header">Filter:</div>
                                <a class="dropdown-item" href="#">This Month</a>
                                <a class="dropdown-item" href="#">This Year</a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="#">All Time</a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="chart-area">
                            <canvas id="casesAreaChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cases by Stage Pie Chart -->
            <div class="col-xl-4 col-lg-5">
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">Cases by Stage</h6>
                    </div>
                    <div class="card-body">
                        <div class="chart-pie pt-4 pb-2">
                            <canvas id="casesByStageChart"></canvas>
                        </div>
                        <div class="mt-4 text-center small">
                            <span class="mr-2">
                                <i class="fas fa-circle text-primary"></i> Inspection
                            </span>
                            <span class="mr-2">
                                <i class="fas fa-circle text-success"></i> Docketing
                            </span>
                            <span class="mr-2">
                                <i class="fas fa-circle text-info"></i> Hearing
                            </span>
                            <span class="mr-2">
                                <i class="fas fa-circle text-warning"></i> Resolution
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Content Row - Location & Priority -->
        <div class="row">

            <!-- Cases by Location -->
            <div class="col-lg-6 mb-4">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Cases by Location</h6>
                    </div>
                    <div class="card-body">
                        <div class="chart-bar">
                            <canvas id="locationChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Case Priority Breakdown -->
            <div class="col-lg-6 mb-4">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Case Priority Levels</h6>
                    </div>
                    <div class="card-body">
                        <h4 class="small font-weight-bold">Critical <span class="float-right">{{ $criticalCases }} cases</span></h4>
                        <div class="progress mb-4">
                            <div class="progress-bar bg-danger" role="progressbar" style="width: {{ $criticalPercentage }}%" 
                                aria-valuenow="{{ $criticalPercentage }}" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <h4 class="small font-weight-bold">High <span class="float-right">{{ $highCases }} cases</span></h4>
                        <div class="progress mb-4">
                            <div class="progress-bar bg-warning" role="progressbar" style="width: {{ $highPercentage }}%" 
                                aria-valuenow="{{ $highPercentage }}" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <h4 class="small font-weight-bold">Medium <span class="float-right">{{ $mediumCases }} cases</span></h4>
                        <div class="progress mb-4">
                            <div class="progress-bar" role="progressbar" style="width: {{ $mediumPercentage }}%" 
                                aria-valuenow="{{ $mediumPercentage }}" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <h4 class="small font-weight-bold">Low <span class="float-right">{{ $lowCases }} cases</span></h4>
                        <div class="progress">
                            <div class="progress-bar bg-success" role="progressbar" style="width: {{ $lowPercentage }}%" 
                                aria-valuenow="{{ $lowPercentage }}" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Cases Table -->
        <div class="row">
            <div class="col-lg-12 mb-4">
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">Recent Cases</h6>
                        <a href="{{ route('case.index') }}" class="btn btn-sm btn-primary">View All Cases</a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>Case No.</th>
                                        <th>Title</th>
                                        <th>Location</th>
                                        <th>Stage</th>
                                        <th>Status</th>
                                        <th>Priority</th>
                                        <th>Created</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($recentCases as $case)
                                    <tr>
                                        <td><strong>{{ $case->case_no ?? 'N/A' }}</strong></td>
                                        <td>{{ Str::limit($case->establishment_name ?? 'No Name', 40) }}</td>
                                        <td>
                                            <i class="fas fa-map-marker-alt text-danger"></i> 
                                            {{ $case->establishment_name ?? 'N/A' }}
                                        </td>
                                        <td>
                                            @if($case->current_stage == 'inspection')
                                                <span class="badge badge-primary">Inspection</span>
                                            @elseif($case->current_stage == 'docketing')
                                                <span class="badge badge-info">Docketing</span>
                                            @elseif($case->current_stage == 'hearing')
                                                <span class="badge badge-warning">Hearing</span>
                                            @elseif($case->current_stage == 'review')
                                                <span class="badge badge-secondary">Review</span>
                                            @elseif($case->current_stage == 'resolution')
                                                <span class="badge badge-success">Resolution</span>
                                            @else
                                                <span class="badge badge-dark">{{ ucfirst($case->current_stage ?? 'N/A') }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($case->overall_status == 'active')
                                                <span class="badge badge-success">Active</span>
                                            @elseif($case->overall_status == 'pending')
                                                <span class="badge badge-warning">Pending</span>
                                            @elseif($case->overall_status == 'closed')
                                                <span class="badge badge-secondary">Closed</span>
                                            @else
                                                <span class="badge badge-info">{{ ucfirst($case->overall_status ?? 'N/A') }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($case->current_stage == 'hearing')
                                                <span class="badge badge-danger">Critical</span>
                                            @elseif($case->current_stage == 'docketing')
                                                <span class="badge badge-warning">High</span>
                                            @elseif($case->current_stage == 'inspection')
                                                <span class="badge badge-primary">Medium</span>
                                            @else
                                                <span class="badge badge-success">Low</span>
                                            @endif
                                        </td>
                                        <td>{{ $case->created_at ? $case->created_at->format('M d, Y') : 'N/A' }}</td>
                                        <td>
                                            <a href="{{ route('case.show', $case->id) }}" class="btn btn-sm btn-info" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @if(in_array(auth()->user()->role, ['admin', 'case_management', 'province']))
                                            <a href="{{ route('case.edit', $case->id) }}" class="btn btn-sm btn-warning" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            @endif
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="8" class="text-center">No recent cases found</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions & Document Tracking -->
        <div class="row">
            <!-- Quick Actions -->
            <div class="col-lg-6 mb-4">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
                    </div>
                    <div class="card-body">
                        @if(in_array(auth()->user()->role, ['admin', 'province', 'malsu', 'case_management']))
                        <a href="{{ route('case.create') }}" class="btn btn-primary btn-icon-split mb-2 btn-block">
                            <span class="icon text-white-50">
                                <i class="fas fa-plus"></i>
                            </span>
                            <span class="text">Create New Case</span>
                        </a>
                        @endif
                        
                        @if(in_array(auth()->user()->role, ['admin', 'malsu', 'case_management', 'province']))
                        <a href="{{ route('inspection.create') }}" class="btn btn-success btn-icon-split mb-2 btn-block">
                            <span class="icon text-white-50">
                                <i class="fas fa-search"></i>
                            </span>
                            <span class="text">Schedule Inspection</span>
                        </a>
                        @endif
                        
                        @if(in_array(auth()->user()->role, ['admin', 'case_management', 'province']))
                        <a href="{{ route('hearing.create') }}" class="btn btn-warning btn-icon-split mb-2 btn-block">
                            <span class="icon text-white-50">
                                <i class="fas fa-gavel"></i>
                            </span>
                            <span class="text">Schedule Hearing</span>
                        </a>
                        @endif
                        
                        <a href="{{ route('documents.tracking') }}" class="btn btn-info btn-icon-split mb-2 btn-block">
                            <span class="icon text-white-50">
                                <i class="fas fa-file-alt"></i>
                            </span>
                            <span class="text">Track Documents</span>
                        </a>
                        
                        <a href="{{ route('archive.index') }}" class="btn btn-secondary btn-icon-split btn-block">
                            <span class="icon text-white-50">
                                <i class="fas fa-archive"></i>
                            </span>
                            <span class="text">View Archives</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Document Tracking Summary -->
            <div class="col-lg-6 mb-4">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Document Status</h6>
                    </div>
                    <div class="card-body">
                        <h4 class="small font-weight-bold">In Transit 
                            <span class="float-right">{{ $documentsInTransit }}</span>
                        </h4>
                        <div class="progress mb-4">
                            <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $documentsInTransitPercent }}%" 
                                aria-valuenow="{{ $documentsInTransitPercent }}" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        
                        <h4 class="small font-weight-bold">Pending Receipt 
                            <span class="float-right">{{ $documentsPending }}</span>
                        </h4>
                        <div class="progress mb-4">
                            <div class="progress-bar bg-warning" role="progressbar" style="width: {{ $documentsPendingPercent }}%" 
                                aria-valuenow="{{ $documentsPendingPercent }}" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        
                        <h4 class="small font-weight-bold">Received 
                            <span class="float-right">{{ $documentsReceived }}</span>
                        </h4>
                        <div class="progress">
                            <div class="progress-bar bg-success" role="progressbar" style="width: {{ $documentsReceivedPercent }}%" 
                                aria-valuenow="{{ $documentsReceivedPercent }}" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        
                        <div class="mt-4 text-center">
                            <a href="{{ route('documents.tracking') }}" class="btn btn-primary btn-sm">
                                View All Documents <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
    <!-- /.container-fluid -->

</div>
<!-- End of Main Content -->

<!-- Footer -->
<footer class="sticky-footer bg-white">
    <div class="container my-auto">
        <div class="copyright text-center my-auto">
            <span>Copyright &copy; Case Management System {{ date('Y') }}</span>
        </div>
    </div>
</footer>
<!-- End of Footer -->

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
console.log('Dashboard scripts section reached');

// Cases Trend Area Chart
var ctx = document.getElementById("casesAreaChart");
var casesAreaChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: {!! json_encode($monthLabels) !!},
        datasets: [{
            label: "Cases Filed",
            lineTension: 0.3,
            backgroundColor: "rgba(78, 115, 223, 0.05)",
            borderColor: "rgba(78, 115, 223, 1)",
            pointRadius: 3,
            pointBackgroundColor: "rgba(78, 115, 223, 1)",
            pointBorderColor: "rgba(78, 115, 223, 1)",
            pointHoverRadius: 3,
            pointHoverBackgroundColor: "rgba(78, 115, 223, 1)",
            pointHoverBorderColor: "rgba(78, 115, 223, 1)",
            pointHitRadius: 10,
            pointBorderWidth: 2,
            data: {!! json_encode($monthlyData) !!},
        }],
    },
    options: {
        maintainAspectRatio: false,
        layout: {
            padding: {
                left: 10,
                right: 25,
                top: 25,
                bottom: 0
            }
        },
        scales: {
            x: {
                grid: {
                    display: false,
                    drawBorder: false
                },
                ticks: {
                    maxTicksLimit: 7
                }
            },
            y: {
                ticks: {
                    maxTicksLimit: 5,
                    padding: 10,
                },
                grid: {
                    color: "rgb(234, 236, 244)",
                    drawBorder: false,
                    borderDash: [2],
                }
            }
        },
        plugins: {
            legend: {
                display: false
            },
            tooltip: {
                backgroundColor: "rgb(255,255,255)",
                bodyColor: "#858796",
                titleMarginBottom: 10,
                titleColor: '#6e707e',
                titleFont: {
                    size: 14
                },
                borderColor: '#dddfeb',
                borderWidth: 1,
                padding: 15,
                displayColors: false,
                intersect: false,
                mode: 'index',
                caretPadding: 10,
            }
        }
    }
});

// Cases by Stage Pie Chart
var ctxPie = document.getElementById("casesByStageChart");
var casesByStageChart = new Chart(ctxPie, {
    type: 'doughnut',
    data: {
        labels: ['Inspection', 'Docketing', 'Hearing', 'Resolution'],
        datasets: [{
            data: {!! json_encode($stageData) !!},
            backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e'],
            hoverBackgroundColor: ['#2e59d9', '#17a673', '#2c9faf', '#f4b619'],
            hoverBorderColor: "rgba(234, 236, 244, 1)",
        }],
    },
    options: {
        maintainAspectRatio: false,
        plugins: {
            tooltip: {
                backgroundColor: "rgb(255,255,255)",
                bodyColor: "#858796",
                borderColor: '#dddfeb',
                borderWidth: 1,
                padding: 15,
                displayColors: false,
                caretPadding: 10,
            },
            legend: {
                display: false
            }
        },
        cutout: '80%',
    },
});

// Cases by Location Bar Chart
var ctxBar = document.getElementById("locationChart");
var locationChart = new Chart(ctxBar, {
    type: 'bar',
    data: {
        labels: {!! json_encode($locationLabels) !!},
        datasets: [{
            label: "Cases",
            backgroundColor: "#4e73df",
            hoverBackgroundColor: "#2e59d9",
            borderColor: "#4e73df",
            data: {!! json_encode($locationData) !!},
        }],
    },
    options: {
        maintainAspectRatio: false,
        layout: {
            padding: {
                left: 10,
                right: 25,
                top: 25,
                bottom: 0
            }
        },
        scales: {
            x: {
                grid: {
                    display: false,
                    drawBorder: false
                },
                ticks: {
                    maxTicksLimit: 6
                }
            },
            y: {
                ticks: {
                    maxTicksLimit: 5,
                    padding: 10,
                },
                grid: {
                    color: "rgb(234, 236, 244)",
                    drawBorder: false,
                    borderDash: [2],
                }
            },
        },
        plugins: {
            legend: {
                display: false
            },
            tooltip: {
                backgroundColor: "rgb(255,255,255)",
                bodyColor: "#858796",
                titleMarginBottom: 10,
                titleColor: '#6e707e',
                borderColor: '#dddfeb',
                borderWidth: 1,
                padding: 15,
                displayColors: false,
                intersect: false,
                mode: 'index',
                caretPadding: 10,
            }
        }
    }
});
</script>
@endpush