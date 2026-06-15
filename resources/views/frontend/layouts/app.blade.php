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

    <link href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">

    <link href="{{ asset('css/sb-admin-2.min.css') }}" rel="stylesheet">
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
            transition: width 0.15s ease-in-out;
        }

        /* Fix for when sidebar is toggled */
        .sidebar.toggled {
            width: 6.5rem !important;
            background: linear-gradient(180deg, #4e73df 10%, #224abe 100%) !important;
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

        /* Hide scrollbar on sidebar but keep it scrollable if items overflow */
        .sidebar::-webkit-scrollbar {
            width: 4px;
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: rgba(255,255,255,0.2);
            border-radius: 4px;
        }

        /* ========================================================== */
        /* THE LAYOUT LOGIC REPAIR DECK                              */
        /* ========================================================== */
        html, body {
            height: 100%;
            margin: 0;
            background-color: #f8f9fc;
        }

        #wrapper {
            min-height: 100vh;
            display: flex;
            width: 100%;
        }

        /* Standard open sidebar margin */
        #content-wrapper {
            margin-left: 224px; 
            transition: margin-left 0.15s ease-in-out, width 0.15s ease-in-out;
            padding-top: 1.5rem;
            min-height: 100vh;         
            display: flex;
            flex-direction: column;
            width: calc(100% - 224px);
        }

        /* Adjusts margins when the sidebar collapses */
        body.sidebar-toggled #content-wrapper {
            margin-left: 6.5rem;
            width: calc(100% - 6.5rem);
        }

        /* Pulls container-fluid layout structures to base */
        #content-wrapper .container-fluid {
            padding-top: 0;
            padding-bottom: 2rem;
            flex: 1 0 auto; 
            display: flex;
            flex-direction: column;
        }
        /* ========================================================== */

    @media (max-width: 768px) {
        #content-wrapper {
            margin-left: 104px !important;
            width: calc(100% - 104px) !important;
        }
    }

    </style>
</head>

<body id="page-top">
    <div id="wrapper">
        <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="{{ route('home') }}">
                <div class="sidebar-brand-text mx-3">CMS</div>
            </a>
            <hr class="sidebar-divider my-0">
            <li class="nav-item active">
                <a class="nav-link" href="{{ route('home') }}">
                    <i class="fas fa-fw fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <hr class="sidebar-divider">
            
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
            @if(Auth::user()->isMalsu())
            <li class="nav-item">
                <a class="nav-link" href="{{ route('labor.index') }}">
                    <i class="fas fa-fw fa-briefcase"></i>
                    <span>Labor Relation Cases</span>
                </a>
            </li>
            @endif
            <li class="nav-item">
                <a class="nav-link" href="{{ route('documents.tracking') }}">
                    <i class="fas fa-fw fa-map-marker-alt"></i>
                    <span>Document Tracking</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('analytics.index') }}">
                    <i class="fas fa-fw fa-chart-bar"></i>
                    <span>Technical Overview</span>
                </a>
            </li>
            <hr class="sidebar-divider">
            @endif

            @if(Auth::user()->isAdmin())
            <li class="nav-item">
                <a class="nav-link" href="{{ route('logs.index') }}">
                    <i class="fas fa-fw fa-history"></i>
                    <span>Audit Logs</span>
                </a>
            </li>
            <hr class="sidebar-divider">
            @endif

            <div class="text-center d-none d-md-inline">
                <button class="rounded-circle border-0" id="sidebarToggle"></button>
            </div>

            <div class="sidebar-spacer"></div>

            <div class="sidebar-user">
                <div class="d-flex align-items-center justify-content-between">
                    <a href="{{ route('profile.index') }}" class="user-profile-link" title="View Profile">
                        <img class="user-avatar" src="{{ asset('img/undraw_profile.svg') }}" alt="Profile">
                        <span class="user-name">{{ Auth::user() ? Auth::user()->fname . ' ' . Auth::user()->lname : 'Guest' }}</span>
                    </a>
                    <a href="#" class="user-logout-btn" data-toggle="modal" data-target="#logoutModal" title="Logout">
                        <i class="fas fa-sign-out-alt"></i>
                    </a>
                </div>
            </div>
        </ul>

        <div id="content-wrapper">


            @yield('content')

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
        </div> </div> <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-easing/1.4.1/jquery.easing.min.js"></script>
    <script src="{{ asset('js/sb-admin-2.min.js') }}"></script>
    <script src="{{ asset('vendor/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('vendor/datatables/dataTables.bootstrap4.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


    @stack('scripts')
</body>
</html>