@extends('frontend.layouts.app')

@section('content')
<div class="container-fluid mt-4">
    <h1 class="h3 mb-4 text-gray-800">System Logs</h1>

    <div class="card shadow">
        <div class="card-body">
            <div class="table-responsive">
                <table id="logsTable" class="table table-bordered text-center">
                    <thead class="table-primary">
                        <tr>
                            <th>ID</th>
                            <th>User</th>
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
                                <td>{{ $log->activity }}</td>
                                <td>{{ $log->ip_address }}</td>
                                <td>{{ Str::limit($log->user_agent, 30) }}</td>
                                <td>{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                {{-- Remove Laravel pagination since DataTables will handle it --}}
                {{-- {{ $logs->links() }} --}}
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
    {{-- Include DataTables JS --}}
    <script src="{{ asset('vendor/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('vendor/datatables/dataTables.bootstrap4.min.js') }}"></script>

    {{-- Initialize DataTable --}}
    <script>
        $(document).ready(function() {
            $('#logsTable').DataTable({
                // Optional: adjust the page length and default order
                "pageLength": 10,
                "order": [[0, "desc"]], // Sort by ID descending
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
