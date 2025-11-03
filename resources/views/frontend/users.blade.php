@extends('frontend.layouts.app')
@section('content')

<div class="container-fluid">

    <!-- Button to open modal -->
    <div class="mb-3">
        <button class="btn btn-primary" data-toggle="modal" data-target="#addUserModal">
            + Add User
        </button>
    </div>
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    @endif

<!-- User Table -->
<div class="card shadow mb-4">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-sm">
                <thead class="thead-light">
                    <tr>
                        <th>#</th>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Last Reset Sent</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $index => $user)
                        <tr>
                            <td>{{ $index+1 }}</td>
                            <td>{{ $user->fname }}</td>
                            <td>{{ $user->lname }}</td>
                            <td>{{ $user->email }}</td>
                            <td>
                                @if($user->role === 'admin')
                                    <span class="badge badge-danger">Admin</span>
                                @elseif($user->role === 'province')
                                    <span class="badge badge-primary">Province</span>
                                @elseif($user->role === 'malsu')
                                    <span class="badge badge-info">MALSU</span>
                                @elseif($user->role === 'case_management')
                                    <span class="badge badge-warning">Case Management</span>
                                @else
                                    <span class="badge badge-secondary">User</span>
                                @endif
                            </td>
                            <td>
                                @if($user->password_reset_sent_at)
                                    <span class="badge badge-info" title="{{ $user->password_reset_sent_at->format('M d, Y h:i A') }}">
                                        {{ $user->password_reset_sent_at->diffForHumans() }}
                                    </span>
                                    @if($user->password)
                                        <span class="badge badge-success ml-1">✓ Set</span>
                                    @else
                                        <span class="badge badge-warning ml-1">Pending</span>
                                    @endif
                                @else
                                    <span class="text-muted">Never</span>
                                @endif
                            </td>
                            <td>
                                <!-- Edit Button -->
                                <button class="btn btn-info btn-sm" 
                                        data-toggle="modal" 
                                        data-target="#editUserModal{{ $user->id }}"
                                        title="Edit User">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                
                                <!-- Reset Password Button -->
                                <button type="button" class="btn btn-warning btn-sm" 
                                        data-toggle="modal" 
                                        data-target="#resetPasswordModal"
                                        data-user-id="{{ $user->id }}"
                                        data-user-name="{{ $user->fname }} {{ $user->lname }}"
                                        data-user-email="{{ $user->email }}"
                                        title="Reset Password">
                                    <i class="fas fa-key"></i> Reset
                                </button>
                                
                                <!-- Delete Button -->
                                <button type="button" class="btn btn-danger btn-sm" 
                                        data-toggle="modal" 
                                        data-target="#deleteUserModal"
                                        data-user-id="{{ $user->id }}"
                                        data-user-name="{{ $user->fname }} {{ $user->lname }}"
                                        data-user-email="{{ $user->email }}"
                                        title="Delete User">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">No users found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

</div>

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1" role="dialog" aria-labelledby="addUserModalLabel" aria-hidden="true">
<div class="modal-dialog modal-md" role="document">
    <div class="modal-content">
    <div class="modal-header">
        <h5 class="modal-title" id="addUserModalLabel">Add New User</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
        </button>
    </div>

    <form method="post" action="{{ route('user.post') }}">
        @csrf
        <div class="modal-body">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="form-group">
                <label for="fname">First Name</label>
                <input type="text" class="form-control" id="fname" name="fname" placeholder="Your first name..." value="{{ old('fname') }}">
            </div>

            <div class="form-group">
                <label for="lname">Last Name</label>
                <input type="text" class="form-control" id="lname" name="lname" placeholder="Your last name..." value="{{ old('lname') }}">
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="text" class="form-control" id="email" name="email" placeholder="Your email..." value="{{ old('email') }}">
            </div>

            <div class="form-group">
                <label for="role">Role</label>
                <select class="form-control" id="role" name="role">
                    <option value="user" {{ old('role') === 'user' ? 'selected' : '' }}>User</option>
                    <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                    <option value="province" {{ old('role') === 'province' ? 'selected' : '' }}>Province</option>
                    <option value="malsu" {{ old('role') === 'malsu' ? 'selected' : '' }}>MALSU</option>
                    <option value="case_management" {{ old('role') === 'case_management' ? 'selected' : '' }}>Case Management</option>
                </select>
            </div>
        </div>

        <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary">Save User</button>
        </div>
    </form>
    </div>
</div>
</div>

<!-- Edit User Modals -->
@foreach($users as $user)
<div class="modal fade" id="editUserModal{{ $user->id }}" tabindex="-1" role="dialog" aria-labelledby="editUserModalLabel{{ $user->id }}" aria-hidden="true">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editUserModalLabel{{ $user->id }}">Edit User</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <form method="POST" action="{{ route('user.update', $user->id) }}" id="editUserForm{{ $user->id }}">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="form-group">
                        <label for="fname{{ $user->id }}">First Name</label>
                        <input type="text" class="form-control" id="fname{{ $user->id }}" 
                               name="fname" value="{{ $user->fname }}" required>
                    </div>

                    <div class="form-group">
                        <label for="lname{{ $user->id }}">Last Name</label>
                        <input type="text" class="form-control" id="lname{{ $user->id }}" 
                               name="lname" value="{{ $user->lname }}" required>
                    </div>

                    <div class="form-group">
                        <label for="email{{ $user->id }}">Email</label>
                        <input type="email" class="form-control" id="email{{ $user->id }}" 
                               name="email" value="{{ $user->email }}" required>
                    </div>

                    <div class="form-group">
                        <label for="role{{ $user->id }}">Role</label>
                        <select class="form-control" id="role{{ $user->id }}" name="role" required>
                            <option value="user" {{ $user->role === 'user' ? 'selected' : '' }}>User</option>
                            <option value="admin" {{ $user->role === 'admin' ? 'selected' : '' }}>Admin</option>
                            <option value="province" {{ $user->role === 'province' ? 'selected' : '' }}>Province</option>
                            <option value="malsu" {{ $user->role === 'malsu' ? 'selected' : '' }}>MALSU</option>
                            <option value="case_management" {{ $user->role === 'case_management' ? 'selected' : '' }}>Case Management</option>
                        </select>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" 
                            data-toggle="modal" 
                            data-target="#confirmEditUserModal"
                            data-user-id="{{ $user->id }}"
                            data-user-name="{{ $user->fname }} {{ $user->lname }}"
                            data-dismiss="modal">
                        Update User
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

<!-- Edit Confirmation Modal -->
<div class="modal fade" id="confirmEditUserModal" tabindex="-1" role="dialog" aria-labelledby="confirmEditUserModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="confirmEditUserModalLabel">Confirm Update</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info" role="alert">
                    <i class="fas fa-info-circle mr-2"></i>
                    <strong>Confirmation Required</strong>
                </div>
                <p>Are you sure you want to update this user's information?</p>
                <p class="text-muted small mb-0">User: <strong><span id="editUserInfo"></span></strong></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmEditBtn">
                    <i class="fas fa-check mr-2"></i>Confirm Update
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteUserModal" tabindex="-1" role="dialog" aria-labelledby="deleteUserModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteUserModalLabel">Delete User</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning" role="alert">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    <strong>Warning!</strong> This action cannot be undone.
                </div>
                <p>Are you sure you want to delete <strong><span id="deleteUserName"></span></strong>?</p>
                <p class="text-muted small mb-0">Email: <strong><span id="deleteUserEmail"></span></strong></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <form method="POST" id="deleteUserForm" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash mr-2"></i>Delete
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Reset Password Confirmation Modal -->
<div class="modal fade" id="resetPasswordModal" tabindex="-1" role="dialog" aria-labelledby="resetPasswordModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="resetPasswordModalLabel">Reset Password</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info" role="alert">
                    <i class="fas fa-info-circle mr-2"></i>
                    <strong>Password Reset Confirmation</strong>
                </div>
                <p>Send password reset link to <strong><span id="resetPasswordEmail"></span></strong>?</p>
                <p class="text-muted small mb-0">User: <strong><span id="resetPasswordUserName"></span></strong></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <form method="POST" id="resetPasswordForm" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-key mr-2"></i>Send Reset Link
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@stop

@section('scripts')
<script>
$(document).ready(function() {
    // Handle Delete Modal
    $('#deleteUserModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var userId = button.data('user-id');
        var userName = button.data('user-name');
        var userEmail = button.data('user-email');
        
        var modal = $(this);
        modal.find('#deleteUserName').text(userName);
        modal.find('#deleteUserEmail').text(userEmail);
        modal.find('#deleteUserForm').attr('action', '{{ route("user.destroy", ":id") }}'.replace(':id', userId));
    });

    // Handle Reset Password Modal
    $('#resetPasswordModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var userId = button.data('user-id');
        var userName = button.data('user-name');
        var userEmail = button.data('user-email');
        
        var modal = $(this);
        modal.find('#resetPasswordEmail').text(userEmail);
        modal.find('#resetPasswordUserName').text(userName);
        modal.find('#resetPasswordForm').attr('action', '{{ route("user.reset-password", ":id") }}'.replace(':id', userId));
    });

    // Handle Edit Confirmation Modal
    var currentEditUserId = null;
    
    $('#confirmEditUserModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        currentEditUserId = button.data('user-id');
        var userName = button.data('user-name');
        
        var modal = $(this);
        modal.find('#editUserInfo').text(userName);
    });

    $('#confirmEditBtn').on('click', function() {
        if (currentEditUserId) {
            $('#editUserForm' + currentEditUserId).submit();
        }
    });
});
</script>
@endsection