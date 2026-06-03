@extends('frontend.layouts.app')
@section('content')

<div id="content" class="d-flex flex-column flex-grow-1 responsive-dashboard-wrapper">
    <div class="container-fluid d-flex flex-column flex-grow-1 responsive-container-wrapper">

        <div class="d-sm-flex align-items-center justify-content-between mb-4 flex-shrink-0">
            <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
            @if($isProvince)
                <span class="badge badge-primary px-3 py-2" style="font-size: 0.85rem;">
                    <i class="fas fa-map-marker-alt mr-1"></i>
                    {{ Auth::user()->getProvinceName() }} Office
                </span>
            @endif
        </div>

        <div class="row flex-shrink-0">

        @if($isProvince)

            <div class="col-xl-4 col-md-6 mb-4">
                <div class="card border-left-secondary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">Total Cases Handled</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalCases }}</div>
                                <div class="mt-1">
                                    <span class="badge" style="font-size: 0.65rem; padding: 0.25rem 0.5rem; background-color: #e67e22; color: white;">
                                        <i class="fas fa-map-marker-alt mr-1"></i>{{ Auth::user()->getProvinceName() }}
                                    </span>
                                </div>
                            </div>
                            <div class="col-auto"><i class="fas fa-folder-open fa-2x text-gray-300"></i></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4 col-md-6 mb-4">
                <div class="card border-left-success shadow h-100 py-2 clickable-card"
                     data-toggle="modal" data-target="#activeCasesModal" style="cursor: pointer;">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Active Cases</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $activeCases }}</div>
                                <div class="mt-1">
                                    <span class="badge" style="font-size: 0.65rem; padding: 0.25rem 0.5rem; background-color: #e67e22; color: white;">
                                        <i class="fas fa-map-marker-alt mr-1"></i>{{ Auth::user()->getProvinceName() }}
                                    </span>
                                </div>
                            </div>
                            <div class="col-auto"><i class="fas fa-clipboard-list fa-2x text-gray-300"></i></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4 col-md-6 mb-4">
                <div class="card shadow h-100 py-2" style="border-left: 4px solid #e67e22;">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-uppercase mb-1" style="color: #e67e22;">Disposed Cases</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $disposedCases }}</div>
                                <div class="mt-1">
                                    <span class="badge" style="font-size: 0.65rem; padding: 0.25rem 0.5rem; background-color: #e67e22; color: white;">
                                        <i class="fas fa-map-marker-alt mr-1"></i>{{ Auth::user()->getProvinceName() }}
                                    </span>
                                </div>
                            </div>
                            <div class="col-auto"><i class="fas fa-landmark fa-2x text-gray-300"></i></div>
                        </div>
                    </div>
                </div>
            </div>

        @else

            {{-- REGIONAL/ADMIN VIEW --}}
            @php $hasCMCard = in_array(Auth::user()->role, ['case_management', 'admin']); @endphp

            <div class="{{ $hasCMCard ? 'col-xl' : 'col-xl-3' }} col-md-6 mb-4">
                <div class="card border-left-success shadow h-100 py-2 clickable-card"
                     data-toggle="modal" data-target="#activeCasesModal" style="cursor: pointer;">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Active Cases</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $activeCases }}</div>
                                <small class="text-muted" style="font-size: 0.7rem; line-height: 1.4;">
                                    <i class="fas fa-globe-asia mr-1"></i>All offices, region-wide
                                </small>
                            </div>
                            <div class="col-auto"><i class="fas fa-clipboard-list fa-2x text-gray-300"></i></div>
                        </div>
                    </div>
                </div>
            </div>

            @if($hasCMCard)
            <div class="col-xl col-md-6 mb-4">
                <div class="card border-left-warning shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Active Cases (Case Management)</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $caseManagementActiveCases }}</div>
                                <small class="text-muted" style="font-size: 0.7rem; line-height: 1.4;">
                                    <i class="fas fa-folder-open mr-1"></i>Currently at Case Management
                                </small>
                            </div>
                            <div class="col-auto"><i class="fas fa-briefcase fa-2x text-gray-300"></i></div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <div class="{{ $hasCMCard ? 'col-xl' : 'col-xl-3' }} col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Disposed Cases (Actual)</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $actualDisposedCases }}</div>
                                <div class="mt-1">
                                    <span class="badge badge-primary" style="font-size: 0.65rem; padding: 0.25rem 0.5rem;">
                                        <i class="fas fa-building mr-1"></i>Regional
                                    </span>
                                </div>
                            </div>
                            <div class="col-auto"><i class="fas fa-gavel fa-2x text-gray-300"></i></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="{{ $hasCMCard ? 'col-xl' : 'col-xl-3' }} col-md-6 mb-4">
                <div class="card border-left-info shadow h-100 py-2 clickable-card"
                     data-toggle="modal" data-target="#misDisposedModal" style="cursor: pointer;">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">MIS Disposed Cases</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $misDisposedCases }}</div>
                                <div class="mt-1">
                                    <span class="badge badge-info" style="font-size: 0.65rem; padding: 0.25rem 0.5rem;">
                                        <i class="fas fa-building mr-1"></i>Regional
                                    </span>
                                </div>
                            </div>
                            <div class="col-auto"><i class="fas fa-database fa-2x text-gray-300"></i></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="{{ $hasCMCard ? 'col-xl' : 'col-xl-3' }} col-md-6 mb-4">
                <div class="card shadow h-100 py-2" style="border-left: 4px solid #e67e22;">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-uppercase mb-1" style="color: #e67e22;">Disposed Cases (Provincial)</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $disposedCases }}</div>
                                <div class="mt-1">
                                    <span class="badge" style="font-size: 0.65rem; padding: 0.25rem 0.5rem; background-color: #e67e22; color: white;">
                                        <i class="fas fa-map-marker-alt mr-1"></i>Provincial
                                    </span>
                                </div>
                            </div>
                            <div class="col-auto"><i class="fas fa-landmark fa-2x text-gray-300"></i></div>
                        </div>
                    </div>
                </div>
            </div>

        @endif

        </div>
        {{-- End Statistics Cards Row --}}

        <div class="row align-items-stretch flex-grow-1 bottom-content-row" style="min-height: 0;">

            <div class="col-xl-8 col-lg-7 d-flex flex-column pb-4 chart-column-wrapper">

                @if($isProvince)
                    {{-- PROVINCIAL VIEW: Cases Overview Chart --}}
                    <div class="card shadow h-100 d-flex flex-column" style="min-height: 0;">
                        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between flex-shrink-0">
                            <h6 class="m-0 font-weight-bold text-primary">
                                {{ Auth::user()->getProvinceName() }} — Cases Overview (Last 6 Months)
                            </h6>
                            <div class="dropdown no-arrow">
                                <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink"
                                    data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                                </a>
                                <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in" aria-labelledby="dropdownMenuLink">
                                    <div class="dropdown-header">Filter:</div>
                                    <a class="dropdown-item" href="#">This Month</a>
                                    <a class="dropdown-item" href="#">This Year</a>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item" href="#">All Time</a>
                                </div>
                            </div>
                        </div>
                        <div class="card-body d-flex flex-column flex-grow-1 card-chart-body" style="min-height: 0; padding: 1.25rem;">
                            <div class="chart-area flex-grow-1" style="position: relative; width: 100%; height: 100%;">
                                <canvas id="casesAreaChart"></canvas>
                            </div>
                        </div>
                    </div>

                @elseif(in_array(Auth::user()->role, ['case_management', 'admin']))
                    {{-- ADMIN / CASE MANAGEMENT VIEW: Province Breakdown Table --}}
                    <div class="card shadow h-100 d-flex flex-column" style="min-height: 0;">
                        <div class="card-header py-3 d-flex align-items-center justify-content-between flex-shrink-0">
                            <h6 class="m-0 font-weight-bold text-primary">Cases by Province</h6>
                            <span class="badge badge-secondary badge-pill">Active caseload</span>
                        </div>
                        <div class="card-body d-flex flex-column" style="min-height: 0; padding: 0;">
                            <div class="table-responsive province-table-wrapper" style="flex: 1 1 0; min-height: 0;">
                                @php $maxProvince = $byProvince->max('total') ?: 1; @endphp
                                <table class="table mb-0" style="font-size: 0.8rem;">
                                    <thead style="background: #f8f9fc;">
                                        <tr>
                                            <th class="pl-4 py-2" style="font-size: 0.7rem; text-transform: uppercase; letter-spacing: .05em; color: #6e707e; font-weight: 600; border-bottom: 2px solid #e3e6f0;">Province</th>
                                            <th class="text-center py-2" style="width:80px; font-size: 0.7rem; text-transform: uppercase; letter-spacing: .05em; color: #6e707e; font-weight: 600; border-bottom: 2px solid #e3e6f0;">Active</th>
                                            <th class="text-center py-2" style="width:90px; font-size: 0.7rem; text-transform: uppercase; letter-spacing: .05em; color: #6e707e; font-weight: 600; border-bottom: 2px solid #e3e6f0;">Disposed<br><span style="font-weight:400;">this month</span></th>
                                            <th class="text-center py-2" style="width:100px; font-size: 0.7rem; text-transform: uppercase; letter-spacing: .05em; color: #e74a3b; font-weight: 600; border-bottom: 2px solid #e3e6f0;"><i class="fas fa-exclamation-circle mr-1"></i>Beyond</th>
                                            <th class="py-2 pr-4" style="font-size: 0.7rem; text-transform: uppercase; letter-spacing: .05em; color: #6e707e; font-weight: 600; border-bottom: 2px solid #e3e6f0;">Caseload</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($byProvince as $prov)
                                        @php
                                            $barPct      = round(($prov['total'] / $maxProvince) * 100);
                                            $barColor    = $prov['active'] > 10 ? '#e74a3b' : ($prov['active'] > 5 ? '#f6c23e' : '#1cc88a');
                                            $badge       = $prov['active'] > 10 ? 'danger' : ($prov['active'] > 5 ? 'warning' : 'success');

                                            $beyondCount = \App\Models\CaseFile::where('po_office', $prov['name'])
                                                ->where('overall_status', 'Active')
                                                ->whereNotNull('pct_96_days')
                                                ->whereDate('pct_96_days', '<', now())
                                                ->distinct()
                                                ->count('id');
                                        @endphp
                                        <tr style="border-bottom: 1px solid #f0f0f0; transition: background .15s;">
                                            <td class="pl-4 align-middle" style="padding-top: 0.55rem; padding-bottom: 0.55rem;">
                                                <div class="font-weight-bold text-dark" style="font-size: 0.9rem;">{{ $prov['name'] }}</div>
                                                <div class="text-muted" style="font-size: 0.72rem;">{{ $prov['total'] }} cases total</div>
                                            </td>
                                            <td class="text-center align-middle">
                                                <span class="badge badge-{{ $badge }}" style="font-size: 0.8rem; padding: .35em .65em; border-radius: 50px;">
                                                    {{ $prov['active'] }}
                                                </span>
                                            </td>
                                            <td class="text-center align-middle" style="color: #858796; font-size: 0.85rem;">
                                                @if($prov['disposed'] > 0)
                                                    <span class="font-weight-bold text-success">{{ $prov['disposed'] }}</span>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td class="text-center align-middle">
                                                @if($beyondCount > 0)
                                                    <span style="display:inline-flex; align-items:center; gap:4px; background:#fff5f5; color:#e74a3b; border:1px solid #f5c6cb; border-radius:50px; padding:.3em .75em; font-size:0.8rem; font-weight:700;">
                                                        <i class="fas fa-exclamation-circle" style="font-size:0.7rem;"></i>{{ $beyondCount }}
                                                    </span>
                                                @else
                                                    <span class="text-muted" style="font-size:0.85rem;">—</span>
                                                @endif
                                            </td>
                                            <td class="align-middle pr-4" style="min-width: 160px;">
                                                <div style="height: 10px; border-radius: 5px; background: #eaecf4; overflow: hidden;">
                                                    <div style="width: {{ $barPct }}%; height: 100%; border-radius: 5px; background: {{ $barColor }}; transition: width .6s ease;"></div>
                                                </div>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                @else
                    {{-- OTHER REGIONAL ROLES: fallback chart --}}
                    <div class="card shadow h-100 d-flex flex-column" style="min-height: 0;">
                        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between flex-shrink-0">
                            <h6 class="m-0 font-weight-bold text-primary">Cases Overview (Last 6 Months)</h6>
                            <div class="dropdown no-arrow">
                                <a class="dropdown-toggle" href="#" role="button" data-toggle="dropdown">
                                    <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                                </a>
                                <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in">
                                    <div class="dropdown-header">Filter:</div>
                                    <a class="dropdown-item" href="#">This Month</a>
                                    <a class="dropdown-item" href="#">This Year</a>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item" href="#">All Time</a>
                                </div>
                            </div>
                        </div>
                        <div class="card-body d-flex flex-column flex-grow-1 card-chart-body" style="min-height: 0; padding: 1.25rem;">
                            <div class="chart-area flex-grow-1" style="position: relative; width: 100%; height: 100%;">
                                <canvas id="casesAreaChart"></canvas>
                            </div>
                        </div>
                    </div>
                @endif

            </div>

            <div class="col-xl-4 col-lg-5 d-flex flex-column pb-4 alerts-column-wrapper">
                <div class="card shadow h-100 d-flex flex-column" style="min-height: 0;">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between flex-shrink-0">
                        <h6 class="m-0 font-weight-bold text-danger">
                            <i class="fas fa-exclamation-triangle"></i> Deadline Alerts
                        </h6>
                        <span class="badge badge-danger badge-pill d-none" id="widgetNotifBadge">0</span>
                    </div>
                    
                    <div class="card-body alerts-card-scrollable" id="widgetNotifItems" style="overflow-y: auto; overflow-x: hidden; padding: 1.25rem;">
                        <div class="text-center text-muted py-4 my-auto" id="widgetNotifEmpty">
                            <i class="fas fa-check-circle fa-3x mb-3 d-block text-success"></i>
                            <p class="mb-0">No cases beyond deadline</p>
                            <small>All caught up!</small>
                        </div>
                    </div>

                    <div class="card-footer text-center bg-white border-top py-2 flex-shrink-0">
                        <a href="{{ route('case.index') }}" class="btn btn-sm btn-link text-primary font-weight-bold" style="font-size: 0.8rem; text-decoration: none;">
                            <i class="fas fa-folder-open mr-1"></i> Go to Active Cases
                        </a>
                    </div>
                </div>
            </div>

        </div>
        {{-- End Charts Row --}}

    </div>
</div>

{{-- ===================================================================== --}}
{{--  MIS DISPOSED CASES MODAL                                             --}}
{{-- ===================================================================== --}}
<div class="modal fade" id="misDisposedModal" tabindex="-1" role="dialog" aria-labelledby="misDisposedModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="misDisposedModalLabel">
                    <i class="fas fa-database mr-2"></i> MIS Disposed Cases
                    <span class="badge badge-light ml-2" style="font-size: 0.85rem;">{{ $misDisposedCases }}</span>
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p class="text-muted mb-3" style="font-size: 0.85rem;">
                    Active cases where a <strong>Date Signed (MIS)</strong> has been recorded.
                </p>
                @if($misDisposedCasesList->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover table-sm">
                            <thead class="thead-light">
                                <tr>
                                    <th>#</th>
                                    <th>Case No.</th>
                                    <th>Inspection ID</th>
                                    <th>Establishment</th>
                                    <th>Provincial Office</th>
                                    <th>Date Signed (MIS)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($misDisposedCasesList as $index => $misCase)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td><strong>{{ $misCase->case_no ?? 'N/A' }}</strong></td>
                                        <td>{{ $misCase->inspection_id ?? 'N/A' }}</td>
                                        <td>{{ $misCase->establishment_name ?? 'N/A' }}</td>
                                        <td>
                                            <span class="badge" style="background-color: #e67e22; color: white; font-size: 0.75rem;">
                                                <i class="fas fa-map-marker-alt mr-1"></i>{{ $misCase->po_office ?? 'N/A' }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="d-block font-weight-bold text-primary">
                                                {{ $misCase->date_signed_mis ? \Carbon\Carbon::parse($misCase->date_signed_mis)->format('M d, Y') : 'N/A' }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-check-circle fa-3x mb-3 d-block text-success"></i>
                        <p class="mb-0">No MIS Disposed cases found</p>
                        <small>All active cases are within the 96-day threshold.</small>
                    </div>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

{{-- ===================================================================== --}}
{{--  ACTIVE CASES DISTRIBUTION MODAL                                      --}}
{{-- ===================================================================== --}}
<div class="modal fade" id="activeCasesModal" tabindex="-1" role="dialog" aria-labelledby="activeCasesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-gradient-primary text-white">
                <h5 class="modal-title" id="activeCasesModalLabel">
                    <i class="fas fa-map-marker-alt mr-2"></i>
                    @if($isProvince)
                        Active Cases — {{ Auth::user()->getProvinceName() }}
                    @else
                        Active Cases by Location / Department
                    @endif
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-4 pb-3 border-bottom">
                    <h4 class="font-weight-bold text-primary mb-1">
                        @if($isProvince)
                            Active Cases in {{ Auth::user()->getProvinceName() }}
                        @else
                            Total Active Cases
                        @endif
                    </h4>
                    <h2 class="display-4 font-weight-bold text-dark mb-0">{{ $activeCases }}</h2>
                </div>

                @if($isProvince)
                    <div class="text-center py-3">
                        <div class="card border-left-success shadow-sm mx-auto" style="max-width: 320px;">
                            <div class="card-body py-4">
                                <i class="fas fa-map-marker-alt fa-3x text-success mb-3 d-block"></i>
                                <h5 class="font-weight-bold text-dark mb-1">{{ Auth::user()->getProvinceName() }} Provincial Office</h5>
                                <p class="text-muted mb-3" style="font-size: 0.85rem;">Cases currently active in your office</p>
                                <h2 class="display-4 font-weight-bold text-success mb-0">{{ $activeCases }}</h2>
                                <small class="text-muted">active cases</small>
                                <hr class="my-3">
                                <div class="row text-center">
                                    <div class="col-6">
                                        <div class="font-weight-bold text-primary">{{ $totalCases }}</div>
                                        <small class="text-muted">Total Handled</small>
                                    </div>
                                    <div class="col-6">
                                        <div class="font-weight-bold text-warning">{{ $disposedCases }}</div>
                                        <small class="text-muted">Disposed</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="row align-items-stretch">
                        <div class="col-md-6 mb-4">
                            <h6 class="font-weight-bold text-muted mb-3 text-uppercase">
                                <i class="fas fa-building mr-2"></i> Central Offices
                            </h6>
                            <div class="list-group list-group-flush shadow-sm">
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div><i class="fas fa-user-shield text-primary mr-2"></i> Admin</div>
                                    <span class="badge badge-primary badge-pill font-weight-bold">{{ $activeByRole['admin'] ?? 0 }}</span>
                                </div>
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div><i class="fas fa-balance-scale text-danger mr-2"></i> MALSU</div>
                                    <span class="badge badge-danger badge-pill font-weight-bold">{{ $activeByRole['malsu'] ?? 0 }}</span>
                                </div>
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div><i class="fas fa-folder-open text-info mr-2"></i> Case Management</div>
                                    <span class="badge badge-info badge-pill font-weight-bold">{{ $activeByRole['case_management'] ?? 0 }}</span>
                                </div>
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div><i class="fas fa-file-alt text-success mr-2"></i> Records</div>
                                    <span class="badge badge-success badge-pill font-weight-bold">{{ $activeByRole['records'] ?? 0 }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <h6 class="font-weight-bold text-muted mb-3 text-uppercase">
                                <i class="fas fa-map-marker-alt mr-2"></i> Provincial Offices
                            </h6>
                            <div class="list-group list-group-flush shadow-sm">
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>Albay</span>
                                    <span class="badge badge-primary badge-pill font-weight-bold">{{ $activeByRole['province_albay'] ?? 0 }}</span>
                                </div>
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>Camarines Sur</span>
                                    <span class="badge badge-primary badge-pill font-weight-bold">{{ $activeByRole['province_camarines_sur'] ?? 0 }}</span>
                                </div>
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>Camarines Norte</span>
                                    <span class="badge badge-primary badge-pill font-weight-bold">{{ $activeByRole['province_camarines_norte'] ?? 0 }}</span>
                                </div>
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>Catanduanes</span>
                                    <span class="badge badge-primary badge-pill font-weight-bold">{{ $activeByRole['province_catanduanes'] ?? 0 }}</span>
                                </div>
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>Masbate</span>
                                    <span class="badge badge-primary badge-pill font-weight-bold">{{ $activeByRole['province_masbate'] ?? 0 }}</span>
                                </div>
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>Sorsogon</span>
                                    <span class="badge badge-primary badge-pill font-weight-bold">{{ $activeByRole['province_sorsogon'] ?? 0 }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <a href="{{ route('documents.tracking') }}" class="btn btn-primary">
                    <i class="fas fa-external-link-alt mr-1"></i> View Full Document Tracking
                </a>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
// Chart initialization
var ctx = document.getElementById("casesAreaChart");
if (ctx) {
    new Chart(ctx, {
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
            layout: { padding: { left: 10, right: 25, top: 25, bottom: 0 } },
            scales: {
                x: { grid: { display: false, drawBorder: false }, ticks: { maxTicksLimit: 7 } },
                y: {
                    ticks: { maxTicksLimit: 5, padding: 10 },
                    grid: { color: "rgb(234, 236, 244)", drawBorder: false, borderDash: [2] }
                }
            },
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: "rgb(255,255,255)",
                    bodyColor: "#858796",
                    titleMarginBottom: 10,
                    titleColor: '#6e707e',
                    titleFont: { size: 14 },
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
}

$(document).ready(function() {
    $('.clickable-card').hover(
        function() { $(this).addClass('shadow-lg').css('transform', 'translateY(-5px)'); },
        function() { $(this).removeClass('shadow-lg').css('transform', 'translateY(0)'); }
    );

    $('#activeCasesModal').on('show.bs.modal', function () {
        $(this).find('.badge-pill').each(function(i) {
            $(this).css({ opacity: 0 }).delay(i * 80).animate({ opacity: 1 }, 400);
        });
    });
});

// =====================================================================
// DEADLINE ALERTS LIVE WIDGET POLLING LOGIC
// =====================================================================
(function () {
    const POLL_INTERVAL_MS = 60000;
    const caseIndexUrl     = "{{ route('case.index') }}";
    const pendingUrl       = "{{ route('notifications.beyond') }}";
    const csrfToken        = $('meta[name="csrf-token"]').attr('content');

    function fetchDeadlineAlerts() {
        $.ajax({
            url: pendingUrl,
            method: 'GET',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            success: function (response) {
                if (!response.success) return;
                renderWidgetAlerts(
                    response.count,
                    response.beyond_cases  || [],
                    response.nearing_cases || []
                );
            },
            error: function () {
                // Silently bypass connection hiccups
            }
        });
    }

    function renderWidgetAlerts(count, beyondCases, nearingCases) {
        const $badge    = $('#widgetNotifBadge');
        const $itemsBox = $('#widgetNotifItems');
        const $empty    = $('#widgetNotifEmpty');

        if (count === 0) {
            $badge.addClass('d-none').text('0');
            $itemsBox.find('.widget-section').remove();
            $empty.show();
            return;
        }

        $badge.text(count).removeClass('d-none');
        $empty.hide();
        $itemsBox.find('.widget-section').remove();

        if (beyondCases.length > 0) {
            let beyondHtml = `
                <div class="widget-section mb-3">
                    <div class="d-flex align-items-center mb-2">
                        <span style="width:12px;height:12px;border-radius:50%;background:#e74a3b;display:inline-block;margin-right:6px;flex-shrink:0;"></span>
                        <strong style="font-size:0.8rem;color:#e74a3b;">Beyond Deadline (${beyondCases.length} case/s)</strong>
                    </div>`;

            beyondCases.forEach(function(item) {
                beyondHtml += `
                    <div class="border-left-danger p-2 mb-2 rounded-sm" style="border-left:3px solid #e74a3b !important;background:#fff5f5;">
                        <div style="font-size:0.8rem;font-weight:700;color:#2d3748;">${item.establishment}</div>
                        <div style="font-size:0.72rem;color:#718096;">Case No: <strong class="text-primary">${item.case_no}</strong> &bull; ${item.po_office}</div>
                        <div class="mt-1">
                            ${item.beyond_fields.map(f => `<span class="badge badge-danger mr-1" style="font-size:0.62rem;">${f}</span>`).join('')}
                        </div>
                    </div>`;
            });

            beyondHtml += `</div>`;
            $itemsBox.append(beyondHtml);
        }

        if (nearingCases.length > 0) {
            let nearingHtml = `
                <div class="widget-section mb-3">
                    <div class="d-flex align-items-center mb-2">
                        <span style="width:12px;height:12px;border-radius:50%;background:#f6c23e;display:inline-block;margin-right:6px;flex-shrink:0;"></span>
                        <strong style="font-size:0.8rem;color:#d4a017;">Upcoming Deadlines — Within 10 Days (${nearingCases.length} case/s)</strong>
                    </div>`;

            nearingCases.forEach(function(item) {
                const fieldText = item.nearing_fields
                    .map(f => `${f.label} (due ${f.due_date} — ${f.days_left} day/s left)`)
                    .join('; ');

                nearingHtml += `
                    <div class="border-left-warning p-2 mb-2 rounded-sm" style="border-left:3px solid #f6c23e !important;background:#fffdf0;">
                        <div style="font-size:0.8rem;font-weight:700;color:#2d3748;">${item.establishment}</div>
                        <div style="font-size:0.72rem;color:#718096;">Case No: <strong class="text-primary">${item.case_no}</strong> &bull; ${item.po_office}</div>
                        <div style="font-size:0.72rem;color:#856404;margin-top:3px;">${fieldText}</div>
                    </div>`;
            });

            nearingHtml += `</div>`;
            $itemsBox.append(nearingHtml);
        }
    }

    fetchDeadlineAlerts();
    setInterval(fetchDeadlineAlerts, POLL_INTERVAL_MS);
})();
</script>

<style>
.clickable-card {
    transition: all 0.3s ease;
}
.clickable-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.15) !important;
}
.clickable-card:active {
    transform: translateY(-2px);
}
.text-orange {
    color: #e67e22;
}

/* ========================================================================= */
/* RESPONSIVE LAYOUT CONTROLLER CRITICAL FIX FOR SMARTPHONES                 */
/* ========================================================================= */

@media (min-width: 992px) {
    .responsive-dashboard-wrapper {
        height: calc(100vh - 1.5rem) !important;
        overflow: hidden !important;
    }
    .responsive-container-wrapper {
        height: 100% !important;
        overflow: hidden !important;
    }
    .bottom-content-row {
        height: 100% !important;
    }
    .chart-column-wrapper, .alerts-column-wrapper {
        height: 100% !important;
    }
    .card-chart-body {
        height: calc(100% - 60px) !important;
    }
    .alerts-card-scrollable {
        flex: 1 1 auto !important;
    }
}

/* Mobile Screens (Max-width 991.98px) -> Unlock flow to scroll normally */
@media (max-width: 991.98px) {
    .responsive-dashboard-wrapper {
        height: auto !important;
        overflow: visible !important;
    }
    .responsive-container-wrapper {
        height: auto !important;
        overflow: visible !important;
    }
    .bottom-content-row {
        height: auto !important;
    }
    .chart-column-wrapper, .alerts-column-wrapper {
        height: auto !important;
    }
    .card-chart-body {
        height: 380px !important; /* Fixed height for the chart area on phone screens */
    }
    .alerts-card-scrollable {
        max-height: 420px !important; /* Limits the alert widget box size on phone screens */
        overflow-y: auto !important;   /* Keeps scrolling inside the widget active */
    }

    /* ========================================================================= */
    /* FIXED MOBILITY OVERRIDES FOR PROVINCE BREAKDOWN CARD                      */
    /* ========================================================================= */
    
    /* Target the column block layout explicitly to enforce render bounding boxes */
    .chart-column-wrapper {
        display: block !important;
        height: auto !important;
    }

    /* Force the card element to display its content elements sequentially */
    .chart-column-wrapper .card {
        display: flex !important;
        flex-direction: column !important;
        height: auto !important;
        min-height: 350px !important; /* Prevents the body box from flatlining */
    }

    /* Release raw flex constraint mapping from desktop presets */
    .chart-column-wrapper .card-body {
        display: block !important;
        height: auto !important;
        flex: none !important; 
        padding: 0 !important;
    }

    /* Define fixed vertical scrolling viewport for mobile devices */
    .province-table-wrapper {
        display: block !important;
        width: 100% !important;
        height: 350px !important;      /* Explicitly forces rendering boundaries */
        max-height: 350px !important;
        overflow-y: auto !important;    /* Enables vertical scroll behavior */
        overflow-x: auto !important;    /* Enables responsive swipe column shift */
        -webkit-overflow-scrolling: touch; 
    }
    /* ========================================================================= */
}
/* ========================================================================= */
</style>
@endpush