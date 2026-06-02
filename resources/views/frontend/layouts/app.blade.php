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

        /* Fix sidebar - make it fixed and non-scrollable with main content */
        .sidebar {
            position: fixed !important;
            top: 0;
            left: 0;
            height: 100vh !important;
            overflow-y: auto;
            overflow-x: hidden;
            display: flex !important;
            flex-direction: column !important;
        }

        /* Spacer to push profile/logout to bottom */
        .sidebar-spacer {
            flex: 1;
        }

        /* Profile + Logout section at sidebar bottom */
        .sidebar-user {
            padding: 1rem 0.75rem;
            border-top: 1px solid rgba(255, 255, 255, 0.15);
            background: rgba(0, 0, 0, 0.1);
        }

        /* Avatar + name — clickable profile link */
        .sidebar-user .user-profile-link {
            display: flex;
            align-items: center;
            color: rgba(255, 255, 255, 0.85);
            text-decoration: none;
            border-radius: 6px;
            padding: 0.3rem 0.4rem;
            transition: background 0.2s, color 0.2s;
            flex: 1;
            min-width: 0;
        }

        .sidebar-user .user-profile-link:hover {
            background: rgba(255, 255, 255, 0.15);
            color: #fff;
            text-decoration: none;
        }

        .sidebar-user .user-avatar {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            border: 2px solid rgba(255,255,255,0.4);
            margin-right: 0.65rem;
            flex-shrink: 0;
        }

        .sidebar-user .user-name {
            font-weight: 700;
            font-size: 0.82rem;
            color: inherit;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Logout icon button */
        .sidebar-user .user-logout-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            color: rgba(255, 255, 255, 0.6);
            text-decoration: none;
            width: 34px;
            height: 34px;
            border-radius: 6px;
            flex-shrink: 0;
            transition: background 0.2s, color 0.2s;
            font-size: 0.9rem;
        }

        .sidebar-user .user-logout-btn:hover {
            background: rgba(231, 74, 59, 0.3);
            color: #ff8a80;
            text-decoration: none;
        }

        /* Collapsed sidebar — hide name, keep avatar and logout icon */
        .sidebar.toggled .sidebar-user .user-name {
            display: none;
        }

        .sidebar.toggled .sidebar-user .user-avatar {
            margin-right: 0;
        }

        /* Push content to the right to account for fixed sidebar */
        #content-wrapper {
            margin-left: 224px;
            transition: margin-left 0.3s;
        }

        /* When sidebar is toggled/collapsed */
        body.sidebar-toggled #content-wrapper {
            margin-left: 6.5rem;
        }

        /* Hide scrollbar on sidebar but keep it scrollable if items overflow */
        .sidebar::-webkit-scrollbar {
            width: 4px;
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: rgba(255,255,255,0.2);
            border-radius: 4px;
        }

        /* Content starts at top — no topbar offset needed */
        #content-wrapper {
            padding-top: 1.5rem;
        }

        #content-wrapper .container-fluid {
            padding-top: 0;
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
            <hr class="sidebar-divider">
            @endif

            <!-- Cases Section -->
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

            <!-- Sidebar toggle button -->
            <div class="text-center d-none d-md-inline">
                <button class="rounded-circle border-0" id="sidebarToggle"></button>
            </div>

            <!-- Spacer pushes user section to bottom -->
            <div class="sidebar-spacer"></div>

            <!-- Profile + Logout pinned to sidebar bottom -->
            <div class="sidebar-user">
                <div class="d-flex align-items-center justify-content-between">
                    <!-- Avatar + name → links to profile -->
                    <a href="{{ route('profile.index') }}" class="user-profile-link" title="View Profile">
                        <img class="user-avatar" src="{{ asset('img/undraw_profile.svg') }}" alt="Profile">
                        <span class="user-name">{{ Auth::user() ? Auth::user()->fname . ' ' . Auth::user()->lname : 'Guest' }}</span>
                    </a>
                    <!-- Logout icon only -->
                    <a href="#" class="user-logout-btn" data-toggle="modal" data-target="#logoutModal" title="Logout">
                        <i class="fas fa-sign-out-alt"></i>
                    </a>
                </div>
            </div>
        </ul>
        <!-- End of Sidebar -->

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Mobile sidebar toggle (visible on small screens only) -->
            <div class="d-md-none px-3 py-2">
                <button id="sidebarToggleTop" class="btn btn-link rounded-circle">
                    <i class="fa fa-bars"></i>
                </button>
            </div>

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
        </div>
        <!-- End of Content Wrapper -->
    </div>
    <!-- End of Page Wrapper -->

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

    <!-- DataTables -->
    <script src="{{ asset('vendor/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('vendor/datatables/dataTables.bootstrap4.min.js') }}"></script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- ============================================ -->
    <!-- Page-Specific Scripts Load Here -->
    <!-- ============================================ -->
    @stack('scripts')

    {{-- ============================================
    NOTIFICATION BELL SCRIPT — commented out for now
    ================================================

    <script>
    (function () {
        const POLL_INTERVAL_MS = 60000;
        const caseIndexUrl     = "{{ route('case.index') }}";
        const pendingUrl       =  "{{ route('notifications.beyond') }}";
        const markSeenUrl      = "{{ route('notifications.markSeen') }}";
        const csrfToken        = "{{ csrf_token() }}";

        let lastCount = 0;

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

        function renderNotifications(count, items) {
            const $badge       = $('#notifBadge');
            const $headerCount = $('#notifHeaderCount');
            const $itemsBox    = $('#notifItems');
            const $empty       = $('#notifEmpty');

            if (count > 0) {
                $badge.text(count > 99 ? '99+' : count).removeClass('d-none');
                $headerCount.text(count + (count === 1 ? ' case' : ' cases')).show();
            } else {
                $badge.addClass('d-none').text('');
                $headerCount.hide().text('');
            }

            if (count > lastCount && lastCount !== null) {
                $('#notifToggle .fa-bell')
                    .addClass('text-danger')
                    .css('animation', 'bell-shake 0.6s ease');
                setTimeout(function () {
                    $('#notifToggle .fa-bell')
                        .removeClass('text-danger')
                        .css('animation', '');
                }, 700);
            }
            lastCount = count;

            $itemsBox.find('.notif-item').remove();

            if (items.length === 0) {
                $empty.show();
                return;
            }

            $empty.hide();

            items.forEach(function (item) {
                const pills = item.beyond_fields.map(function (label) {
                    return '<span class="badge badge-danger mr-1" style="font-size:0.7rem;">' + label + '</span>';
                }).join('');

                const html =
                    '<a class="dropdown-item d-flex align-items-start notif-item py-2" ' +
                       'href="' + caseIndexUrl + '" ' +
                       'style="white-space:normal; border-bottom:1px solid #f3f3f3; text-decoration:none;">' +
                        '<div class="mr-3 mt-1 flex-shrink-0">' +
                            '<div style="width:36px;height:36px;display:flex;align-items:center;' +
                                        'justify-content:center;border-radius:50%;background:#e74a3b;">' +
                                '<i class="fas fa-exclamation text-white" style="font-size:0.85rem;"></i>' +
                            '</div>' +
                        '</div>' +
                        '<div style="flex:1; min-width:0;">' +
                            '<div class="font-weight-bold text-dark" ' +
                                 'style="font-size:0.82rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;" ' +
                                 'title="' + item.establishment + '">' +
                                item.establishment +
                            '</div>' +
                            '<div class="small text-gray-600">' +
                                'Case: <strong>' + item.case_no + '</strong>' +
                                ' &nbsp;|&nbsp; <span class="text-muted">' + item.po_office + '</span>' +
                            '</div>' +
                            '<div class="mt-1">' + pills + '</div>' +
                            '<div class="small text-muted mt-1">' +
                                '<i class="fas fa-clock mr-1"></i>Updated ' + item.updated_at +
                            '</div>' +
                        '</div>' +
                    '</a>';

                $itemsBox.append(html);
            });
        }

        $(document).on('shown.bs.dropdown', '#notificationDropdown', function () {
            $.post(markSeenUrl, { _token: csrfToken });
        });

        $('<style>').text(
            '@keyframes bell-shake {' +
            '  0%,100% { transform: rotate(0deg); }' +
            '  20%     { transform: rotate(-18deg); }' +
            '  40%     { transform: rotate(18deg); }' +
            '  60%     { transform: rotate(-10deg); }' +
            '  80%     { transform: rotate(10deg); }' +
            '}'
        ).appendTo('head');

        fetchNotifications();
        setInterval(fetchNotifications, POLL_INTERVAL_MS);

    })();
    </script>
    ============================================ --}}

</body>

</html>