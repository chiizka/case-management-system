<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Case Management System</title>

    <!-- Custom fonts for this template-->
    <link href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="{{ asset('css/sb-admin-2.min.css') }}" rel="stylesheet">

    <!-- Custom styles for DataTables -->
    <link href="{{ asset('vendor/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet">

    <style>
        /* Force solid background on sidebar */
        .sidebar {
            background: linear-gradient(180deg, #4e73df 10%, #224abe 100%) !important;
            background-color: #4e73df !important;
            z-index: 1050 !important;
        }

        /* Ensure all sidebar inner elements have proper background */
        .sidebar .nav-item,
        .sidebar .sidebar-brand,
        .sidebar .sidebar-heading {
            background-color: transparent;
            position: relative;
            z-index: 1051;
        }

        /* Fix for when sidebar is toggled */
        .sidebar.toggled {
            width: 6.5rem !important;
            background: linear-gradient(180deg, #4e73df 10%, #224abe 100%) !important;
        }
    </style>
</head>


<body id="page-top">
    <!-- Page Wrapper -->
    <div id="wrapper">
        <!-- Sidebar -->
        <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="{{ route('home') }}">
                <div class="sidebar-brand-text mx-3">CMS</div>
            </a>
            <hr class="sidebar-divider my-0">
            <li class="nav-item active">
                <a class="nav-link" href="{{ route('home') }}">
                    <i class="fas fa-fw fa-tachometer-alt"></i>
                    <span>Dashboard</span></a>
            </li>
            <hr class="sidebar-divider">
            
            <!-- User Management Section - Only for Admin -->
            @if(Auth::user()->isAdmin())
            <div class="sidebar-heading">User Management</div>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('users') }}">
                    <i class="fas fa-fw fa-users"></i>
                    <span>Users</span>
                </a>
            </li>
            {{-- <li class="nav-item">
                <a class="nav-link" href="cards.html">
                    <i class="fas fa-fw fa-user-tag"></i>
                    <span>Roles</span>
                </a>
            </li> --}}
            <hr class="sidebar-divider">
            @endif

            <!-- Cases Section - Available for Admin, Province, MALSU, Case Management, Records -->
            @if(Auth::user()->isAdmin() || Auth::user()->isProvince() || Auth::user()->isMalsu() || Auth::user()->isCaseManagement() || Auth::user()->isRecords())
            <div class="sidebar-heading">Cases</div>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('case.index') }}">
                    <i class="fas fa-fw fa-folder-open"></i>
                    <span>Active Cases</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('archive.index') }}">
                    <i class="fas fa-fw fa-archive"></i>
                    <span>Archived Cases</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('documents.tracking') }}">
                    <i class="fas fa-fw fa-map-marker-alt"></i>
                    <span>Document Tracking</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('analytics.index') }}">
                    <i class="fas fa-fw fa-chart-bar"></i>
                    <span>Cases Overview</span>
                </a>
            </li>
                        <hr class="sidebar-divider">
            @endif

            <!-- Audit Logs - Only for Admin -->
            @if(Auth::user()->isAdmin())
            <li class="nav-item">
                <a class="nav-link" href="{{ route('logs.index') }}">
                    <i class="fas fa-fw fa-history"></i>
                    <span>Audit Logs</span>
                </a>
            </li>
            <hr class="sidebar-divider">
            @endif

            {{-- <div class="sidebar-heading">Addons</div>
            
            <!-- Pages - Available for All -->
            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapsePages" aria-expanded="true" aria-controls="collapsePages">
                    <i class="fas fa-fw fa-folder"></i>
                    <span>Pages</span>
                </a>
                <div id="collapsePages" class="collapse" aria-labelledby="headingPages" data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded">
                        <h6 class="collapse-header">Login Screens:</h6>
                        <a class="collapse-item" href="login.html">Login</a>
                        <a class="collapse-item" href="register.html">Register</a>
                        <a class="collapse-item" href="forgot-password.html">Forgot Password</a>
                        <div class="collapse-divider"></div>
                        <h6 class="collapse-header">Other Pages:</h6>
                        <a class="collapse-item" href="404.html">404 Page</a>
                        <a class="collapse-item" href="blank.html">Blank Page</a>
                    </div>
                </div>
            </li>

            <!-- Charts - Only for Admin -->
            @if(Auth::user()->isAdmin())
            <li class="nav-item">
                <a class="nav-link" href="charts.html">
                    <i class="fas fa-fw fa-chart-area"></i>
                    <span>Charts</span></a>
            </li>
            @endif

            <!-- Tables - Available for Admin and Case Management -->
            @if(Auth::user()->isAdmin() || Auth::user()->isCaseManagement())
            <li class="nav-item">
                <a class="nav-link" href="tables.html">
                    <i class="fas fa-fw fa-table"></i>
                    <span>Tables</span></a>
            </li>
            @endif --}}

            {{-- <hr class="sidebar-divider d-none d-md-block"> --}}
            <div class="text-center d-none d-md-inline">
                <button class="rounded-circle border-0" id="sidebarToggle"></button>
            </div>
        </ul>
        <!-- End of Sidebar -->

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">
            <!-- Topbar -->
            <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
                <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                    <i class="fa fa-bars"></i>
                </button>
                {{-- <form class="d-none d-sm-inline-block form-inline mr-auto ml-md-3 my-2 my-md-0 mw-100 navbar-search">
                    <div class="input-group">
                        <input type="text" class="form-control bg-light border-0 small" placeholder="Search for..." aria-label="Search" aria-describedby="basic-addon2">
                        <div class="input-group-append">
                            <button class="btn btn-primary" type="button">
                                <i class="fas fa-search fa-sm"></i>
                            </button>
                        </div>
                    </div>
                </form> --}}
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item dropdown no-arrow d-sm-none">
                        <a class="nav-link dropdown-toggle" href="#" id="searchDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-search fa-fw"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right p-3 shadow animated--grow-in" aria-labelledby="searchDropdown">
                            <form class="form-inline mr-auto w-100 navbar-search">
                                <div class="input-group">
                                    <input type="text" class="form-control bg-light border-0 small" placeholder="Search for..." aria-label="Search" aria-describedby="basic-addon2">
                                    <div class="input-group-append">
                                        <button class="btn btn-primary" type="button">
                                            <i class="fas fa-search fa-sm"></i>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </li>
                    <!-- Notification Bell -->
                    <li class="nav-item dropdown no-arrow mx-1" id="notificationDropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="notifToggle" role="button"
                        data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-bell fa-fw"></i>
                            <span class="badge badge-danger badge-counter d-none" id="notifBadge"></span>
                        </a>

                        <div class="dropdown-list dropdown-menu dropdown-menu-right shadow animated--grow-in"
                            aria-labelledby="notifToggle" style="min-width: 340px; max-width: 380px;">

                            <h6 class="dropdown-header d-flex justify-content-between align-items-center">
                                <span>Pending Documents</span>
                                <span class="badge badge-pill badge-warning" id="notifHeaderCount"></span>
                            </h6>

                            <!-- Items injected here by JS -->
                            <div id="notifItems">
                                <div class="dropdown-item text-center text-muted py-3" id="notifEmpty">
                                    <i class="fas fa-check-circle text-success mr-1"></i> No pending documents
                                </div>
                            </div>

                            <a class="dropdown-item text-center small text-gray-500 py-2"
                            href="{{ route('documents.tracking') }}">
                                <i class="fas fa-map-marker-alt mr-1"></i> Go to Document Tracking
                            </a>
                        </div>
                    </li>
                    <div class="topbar-divider d-none d-sm-block"></div>
                    <li class="nav-item dropdown no-arrow">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <span class="mr-2 d-none d-lg-inline text-gray-600 small">
                                {{ Auth::user() ? Auth::user()->fname . ' ' . Auth::user()->lname : 'Guest' }}
                            </span>
                            <img class="img-profile rounded-circle" src="img/undraw_profile.svg">
                        </a>
                        <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown">
                            <a class="dropdown-item" href="{{ route('profile.index') }}">
                                <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                                Profile
                            </a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="#" data-toggle="modal" data-target="#logoutModal">
                                <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                                Logout
                            </a>
                        </div>
                    </li>
                </ul>
            </nav>
            <!-- End of Topbar -->

            @yield('content')

            <!-- Logout Modal-->
            <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLabel">Ready to Leave?</h5>
                            <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">×</span>
                            </button>
                        </div>
                        <div class="modal-body">Select "Logout" below if you are ready to end your current session.</div>
                        <div class="modal-footer">
                            <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                            <form action="{{ route('logout') }}" method="POST" style="display: inline;">
                                @csrf
                                <button type="submit" class="btn btn-primary">Logout</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            
    <!-- ============================================ -->
    <!-- CRITICAL: Load Core JavaScript Libraries FIRST -->
    <!-- ============================================ -->

    <!-- jQuery (MUST be first!) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Bootstrap Bundle (includes Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- jQuery Easing -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-easing/1.4.1/jquery.easing.min.js"></script>

    <!-- SB Admin 2 -->
    <script src="{{ asset('js/sb-admin-2.min.js') }}"></script>

    <!-- DataTables - ONLY if used on multiple pages -->
    <script src="{{ asset('vendor/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('vendor/datatables/dataTables.bootstrap4.min.js') }}"></script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- ============================================ -->
    <!-- Page-Specific Scripts Load Here -->
    <!-- ============================================ -->
    @stack('scripts')
    <script>
    (function () {
        // ─── Config ───────────────────────────────────────────────────
        const POLL_INTERVAL_MS = 60000; // poll every 60 seconds
        const trackingUrl      = "{{ route('documents.tracking') }}";
        const pendingUrl       = "{{ route('notifications.pending') }}";
        const markSeenUrl      = "{{ route('notifications.markSeen') }}";
        const csrfToken        = "{{ csrf_token() }}";

        let lastCount = 0;

        // ─── Fetch pending notifications ──────────────────────────────
        function fetchNotifications() {
            $.ajax({
                url: pendingUrl,
                method: 'GET',
                headers: { 'X-CSRF-TOKEN': csrfToken },
                success: function (response) {
                    if (!response.success) return;
                    renderNotifications(response.count, response.items);
                },
                error: function () {
                    // Silently fail — don't disrupt the UI
                }
            });
        }

        // ─── Render bell badge + dropdown items ───────────────────────
        function renderNotifications(count, items) {
            const $badge        = $('#notifBadge');
            const $headerCount  = $('#notifHeaderCount');
            const $itemsBox     = $('#notifItems');
            const $empty        = $('#notifEmpty');

            // Badge
            if (count > 0) {
                $badge.text(count > 9 ? '9+' : count).removeClass('d-none');
                $headerCount.text(count + ' pending').show();
            } else {
                $badge.addClass('d-none').text('');
                $headerCount.text('').hide();
            }

            // Pulse the bell once when count increases
            if (count > lastCount && lastCount !== null) {
                $('#notifToggle .fa-bell')
                    .addClass('text-warning')
                    .css('animation', 'bell-shake 0.5s ease');
                setTimeout(() => {
                    $('#notifToggle .fa-bell')
                        .removeClass('text-warning')
                        .css('animation', '');
                }, 600);
            }
            lastCount = count;

            // Items
            if (items.length === 0) {
                $empty.show();
                $itemsBox.find('.notif-item').remove();
                return;
            }

            $empty.hide();
            $itemsBox.find('.notif-item').remove();

            items.forEach(function (item) {
                const html = `
                    <a class="dropdown-item d-flex align-items-start notif-item py-2"
                    href="${trackingUrl}"
                    style="white-space: normal; border-bottom: 1px solid #f0f0f0;">
                        <div class="mr-3 mt-1">
                            <div class="icon-circle bg-warning" style="width:36px;height:36px;display:flex;align-items:center;justify-content:center;border-radius:50%;">
                                <i class="fas fa-file-alt text-white" style="font-size:0.85rem;"></i>
                            </div>
                        </div>
                        <div style="flex:1; min-width:0;">
                            <div class="font-weight-bold text-dark"
                                style="font-size:0.82rem; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;"
                                title="${item.establishment}">
                                ${item.establishment}
                            </div>
                            <div class="small text-gray-600">
                                Case: <strong>${item.case_no}</strong>
                            </div>
                            <div class="small text-muted">
                                From: ${item.transferred_by}
                            </div>
                            <div class="small text-muted">
                                <i class="fas fa-clock mr-1"></i>${item.transferred_at}
                            </div>
                        </div>
                    </a>
                `;
                $itemsBox.append(html);
            });
        }

        // ─── Mark seen when dropdown opens ────────────────────────────
        $(document).on('shown.bs.dropdown', '#notificationDropdown', function () {
            $.post(markSeenUrl, { _token: csrfToken });
        });

        // ─── Bell shake animation ──────────────────────────────────────
        $('<style>')
            .text(`
                @keyframes bell-shake {
                    0%,100% { transform: rotate(0deg); }
                    20%      { transform: rotate(-15deg); }
                    40%      { transform: rotate(15deg); }
                    60%      { transform: rotate(-10deg); }
                    80%      { transform: rotate(10deg); }
                }
            `)
            .appendTo('head');

        // ─── Init ─────────────────────────────────────────────────────
        fetchNotifications();
        setInterval(fetchNotifications, POLL_INTERVAL_MS);

    })();
    </script>
        
</body>

</html>