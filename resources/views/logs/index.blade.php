@extends('frontend.layouts.app')

@section('content')
<div class="container-fluid mt-1">
    <h1 class="h3 mb-4 text-gray-800">System Logs</h1>

    <div class="card shadow">
        <div class="card-body">
            <div class="table-responsive">
                <table id="logsTable" class="table table-bordered text-center">
                    <thead class="table-primary">
                        <tr>
                            <th>ID</th>
                            <th>User</th>
                            <th>Action</th>
                            <th>Activity</th>
                            <th>IP Address</th>
                            <th>User Agent</th>
                            <th>Timestamp</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($logs as $log)
                            <tr>
                                <td>{{ $log->id }}</td>
                                <td>{{ $log->user ? $log->user->fname . ' ' . $log->user->lname : 'Guest' }}</td>
                                <td>
                                    @php
                                        $actionClass = match($log->action) {
                                            'create' => 'badge bg-success text-white',
                                            'update' => 'badge bg-primary text-white',
                                            'delete' => 'badge bg-danger text-white',
                                            'view' => 'badge bg-info text-white',
                                            'progress' => 'badge bg-warning text-white',
                                            'login' => 'badge bg-success text-white',
                                            'logout' => 'badge bg-secondary text-white',
                                            'export' => 'badge bg-dark text-white',
                                            'import' => 'badge bg-dark text-white',
                                            default => 'badge bg-secondary text-white'
                                        };
                                    @endphp
                                    <span class="{{ $actionClass }}">{{ strtoupper($log->action ?? 'N/A') }}</span>
                                </td>
                                <td>{{ $log->activity }}</td>
                                <td>{{ $log->ip_address }}</td>
                                <td>{{ Str::limit($log->user_agent, 30) }}</td>
                                <td>{{ $log->created_at->timezone('Asia/Manila')->format('Y-m-d h:i:s A') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
    <script src="{{ asset('vendor/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('vendor/datatables/dataTables.bootstrap4.min.js') }}"></script>

    <script>
        $(document).ready(function() {
            $('#logsTable').DataTable({
                "pageLength": 10,
                "order": [[0, "desc"]],
                "columnDefs": [
                    { "orderable": false, "targets": [4, 5] } // User Agent column not sortable
                ],
                "language": {
                    "search": "Search logs:",
                    "lengthMenu": "Show _MENU_ entries per page",
                    "zeroRecords": "No matching logs found",
                    "info": "Showing _START_ to _END_ of _TOTAL_ logs",
                    "infoEmpty": "No logs available",
                    "infoFiltered": "(filtered from _MAX_ total logs)"
                }
            });
        });
    </script>
@endsection