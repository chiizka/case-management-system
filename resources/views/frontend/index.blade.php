@extends('frontend.layouts.app')
@section('content')

<div id="content">
    <!-- Begin Page Content -->
    <div class="container-fluid">

        <!-- Page Heading -->
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
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

            <!-- Active Cases Card - ENTIRE CARD IS NOW CLICKABLE -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-success shadow h-100 py-2 clickable-card" 
                     data-toggle="modal" 
                     data-target="#activeCasesModal"
                     style="cursor: pointer;">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                    Active Cases</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ $activeCases }}
                                </div>
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
                <div class="card shadow mb-4" style="height: 500px;">
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
                    <div class="card-body" style="height: calc(100% - 60px);">
                        <div class="chart-area" style="height: 100%;">
                            <canvas id="casesAreaChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pending Documents Widget -->
            <div class="col-xl-4 col-lg-5">
                <div class="card shadow mb-4" style="height: 500px; display: flex; flex-direction: column;">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-warning">
                            <i class="fas fa-clock"></i> Pending Documents
                        </h6>
                        @if($totalPendingDocs > 0)
                            <span class="badge badge-warning badge-pill">{{ $totalPendingDocs }}</span>
                        @endif
                    </div>
                    <div class="card-body" style="flex: 1; overflow-y: auto; overflow-x: hidden;">
                        @forelse($pendingDocuments as $doc)
                            <div class="border-left-warning shadow-sm p-3 mb-3" style="border-left: 4px solid #f6c23e !important;">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <h6 class="font-weight-bold text-primary mb-1">{{ $doc->case->case_no ?? 'N/A' }}</h6>
                                        <p class="text-sm text-gray-800 mb-1" style="font-size: 0.85rem;">
                                            {{ Str::limit($doc->case->establishment_name ?? 'N/A', 35) }}
                                        </p>
                                    </div>
                                </div>
                                
                                <div class="row text-sm mb-2" style="font-size: 0.75rem;">
                                    <div class="col-6">
                                        <small class="text-muted d-block">From:</small>
                                        <strong>{{ $doc->transferredBy ? $doc->transferredBy->fname : 'System' }}</strong>
                                    </div>
                                    <div class="col-6 text-right">
                                        <small class="text-muted d-block">Waiting:</small>
                                        @if($doc->transferred_at)
                                            @php
                                                $days = floor($doc->transferred_at->diffInDays(now()));
                                                $badgeClass = $days > 7 ? 'danger' : ($days > 3 ? 'warning' : 'success');
                                            @endphp
                                            <span class="badge badge-{{ $badgeClass }}">{{ $days }} days</span>
                                        @else
                                            <span class="badge badge-secondary">N/A</span>
                                        @endif
                                    </div>
                                </div>
                                
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">{{ $doc->transferred_at ? $doc->transferred_at->format('M d, Y') : 'N/A' }}</small>
                                    <button class="btn btn-sm btn-success receive-doc-btn" 
                                            data-doc-id="{{ $doc->id }}"
                                            data-case-no="{{ $doc->case->case_no ?? 'N/A' }}"
                                            style="font-size: 0.75rem; padding: 0.25rem 0.75rem;">
                                        <i class="fas fa-check"></i> Receive
                                    </button>
                                </div>
                            </div>
                        @empty
                            <div class="text-center text-muted py-4">
                                <i class="fas fa-check-circle fa-3x mb-3 d-block text-success"></i>
                                <p class="mb-0">No pending documents</p>
                                <small>All caught up!</small>
                            </div>
                        @endforelse
                        
                        @if($totalPendingDocs > 5)
                            <div class="text-center mt-3">
                                <a href="{{ route('documents.tracking') }}" class="btn btn-sm btn-outline-primary">
                                    View All {{ $totalPendingDocs }} Documents <i class="fas fa-arrow-right"></i>
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

    </div>
    <!-- /.container-fluid -->

</div>
<!-- End of Main Content -->

<!-- ===================================================================== -->
<!--                  ACTIVE CASES DISTRIBUTION MODAL                      -->
<!-- ===================================================================== -->

<div class="modal fade" id="activeCasesModal" tabindex="-1" role="dialog" aria-labelledby="activeCasesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-gradient-primary text-white">
                <h5 class="modal-title" id="activeCasesModalLabel">
                    <i class="fas fa-map-marker-alt mr-2"></i> 
                    Active Cases by Location / Department
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>

            <div class="modal-body">
                <!-- Total summary -->
                <div class="text-center mb-4 pb-3 border-bottom">
                    <h4 class="font-weight-bold text-primary mb-1">
                        Total Active Cases
                    </h4>
                    <h2 class="display-4 font-weight-bold text-dark mb-0">
                        {{ $activeCases }}
                    </h2>
                </div>

                <div class="row">

                    <!-- Central Offices -->
                    <div class="col-md-6 mb-4">
                        <h6 class="font-weight-bold text-muted mb-3 text-uppercase">
                            <i class="fas fa-building mr-2"></i> Central Offices
                        </h6>

                        <div class="list-group list-group-flush shadow-sm">
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="fas fa-user-shield text-primary mr-2"></i>
                                    Admin
                                </div>
                                <span class="badge badge-primary badge-pill font-weight-bold">
                                    {{ $activeByRole['admin'] ?? 0 }}
                                </span>
                            </div>

                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="fas fa-balance-scale text-danger mr-2"></i>
                                    MALSU
                                </div>
                                <span class="badge badge-danger badge-pill font-weight-bold">
                                    {{ $activeByRole['malsu'] ?? 0 }}
                                </span>
                            </div>

                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="fas fa-folder-open text-info mr-2"></i>
                                    Case Management
                                </div>
                                <span class="badge badge-info badge-pill font-weight-bold">
                                    {{ $activeByRole['case_management'] ?? 0 }}
                                </span>
                            </div>

                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="fas fa-file-alt text-success mr-2"></i>
                                    Records
                                </div>
                                <span class="badge badge-success badge-pill font-weight-bold">
                                    {{ $activeByRole['records'] ?? 0 }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Provincial Offices -->
                    <div class="col-md-6 mb-4">
                        <h6 class="font-weight-bold text-muted mb-3 text-uppercase">
                            <i class="fas fa-map-marker-alt mr-2"></i> Provincial Offices
                        </h6>

                        <div class="list-group list-group-flush shadow-sm">
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <span>Albay</span>
                                <span class="badge badge-primary badge-pill font-weight-bold">
                                    {{ $activeByRole['province_albay'] ?? 0 }}
                                </span>
                            </div>

                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <span>Camarines Sur</span>
                                <span class="badge badge-primary badge-pill font-weight-bold">
                                    {{ $activeByRole['province_camarines_sur'] ?? 0 }}
                                </span>
                            </div>

                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <span>Camarines Norte</span>
                                <span class="badge badge-primary badge-pill font-weight-bold">
                                    {{ $activeByRole['province_camarines_norte'] ?? 0 }}
                                </span>
                            </div>

                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <span>Catanduanes</span>
                                <span class="badge badge-primary badge-pill font-weight-bold">
                                    {{ $activeByRole['province_catanduanes'] ?? 0 }}
                                </span>
                            </div>

                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <span>Masbate</span>
                                <span class="badge badge-primary badge-pill font-weight-bold">
                                    {{ $activeByRole['province_masbate'] ?? 0 }}
                                </span>
                            </div>

                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <span>Sorsogon</span>
                                <span class="badge badge-primary badge-pill font-weight-bold">
                                    {{ $activeByRole['province_sorsogon'] ?? 0 }}
                                </span>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <a href="{{ route('documents.tracking') }}" class="btn btn-primary">
                    <i class="fas fa-external-link-alt mr-1"></i> 
                    View Full Document Tracking
                </a>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>

if (typeof $ === 'undefined') {
    console.error('jQuery is not loaded!');
}
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

// Receive document functionality - FIXED VERSION
$(document).ready(function() {
    console.log('Document ready - setting up receive handlers');
    
    let docToReceive = null;
    
    // Receive button click handler
    $(document).on('click', '.receive-doc-btn', function(e) {
        e.preventDefault();
        console.log('Receive button clicked');
        
        docToReceive = $(this).data('doc-id');
        const caseNo = $(this).data('case-no');
        
        console.log('Document ID:', docToReceive);
        console.log('Case No:', caseNo);
        
        // Show confirmation using SweetAlert2 if available, otherwise use native confirm
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Receive Document?',
                text: 'Case: ' + caseNo,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, receive it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    receiveDocument(docToReceive, caseNo);
                }
            });
        } else {
            // Fallback to native confirm
            if (confirm('Receive document for case: ' + caseNo + '?')) {
                receiveDocument(docToReceive, caseNo);
            }
        }
    });
    
    // Function to receive document
    function receiveDocument(docId, caseNo) {
        console.log('Attempting to receive document:', docId);
        
        // Show loading state
        const button = $(`.receive-doc-btn[data-doc-id="${docId}"]`);
        const originalHtml = button.html();
        button.html('<i class="fas fa-spinner fa-spin"></i> Receiving...').prop('disabled', true);
        
        $.ajax({
            url: '/documents/' + docId + '/receive',
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Accept': 'application/json'
            },
            success: function(response) {
                console.log('Success response:', response);
                
                // Show success message
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message || 'Document received successfully!',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    alert(response.message || 'Document received successfully!');
                    location.reload();
                }
            },
            error: function(xhr, status, error) {
                console.error('Error receiving document:', xhr, status, error);
                
                // Restore button
                button.html(originalHtml).prop('disabled', false);
                
                let errorMsg = 'Failed to receive document.';
                
                if (xhr.responseJSON?.message) {
                    errorMsg = xhr.responseJSON.message;
                } else if (xhr.responseText) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        errorMsg = response.message || errorMsg;
                    } catch(e) {
                        errorMsg = xhr.statusText || errorMsg;
                    }
                }
                
                console.error('Error message:', errorMsg);
                
                // Show error message
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: errorMsg
                    });
                } else {
                    alert(errorMsg);
                }
            }
        });
    }
});

var ctx = document.getElementById("casesAreaChart");
if (ctx) {
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
}

// ────────────────────────────────────────────────
// Hover effect for clickable card
// ────────────────────────────────────────────────
$('.clickable-card').hover(
    function() {
        $(this).addClass('shadow-lg').css('transform', 'translateY(-5px)');
    },
    function() {
        $(this).removeClass('shadow-lg').css('transform', 'translateY(0)');
    }
);

// ────────────────────────────────────────────────
// Optional: subtle animation when modal opens
// ────────────────────────────────────────────────
$('#activeCasesModal').on('show.bs.modal', function () {
    $(this).find('.badge-pill').each(function(i) {
        $(this).css({ opacity: 0, transform: 'scale(0.7)' })
               .delay(i * 80)
               .animate({ opacity: 1, transform: 'scale(1)' }, 400);
    });
});

</script>

<style>
/* Smooth transition for clickable card */
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
</style>

@endpush