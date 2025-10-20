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
                                <form method="POST" action="{{ route('user.reset-password', $user->id) }}" style="display: inline;">
                                    @csrf
                                    <button type="submit" class="btn btn-warning btn-sm" 
                                            onclick="return confirm('Send password reset link to {{ $user->email }}?')"
                                            title="Reset Password">
                                        <i class="fas fa-key"></i> Reset
                                    </button>
                                </form>
                                
                                <!-- Delete Button -->
                                <form method="POST" action="{{ route('user.destroy', $user->id) }}" style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm" 
                                            onclick="return confirm('Are you sure you want to delete {{ $user->fname }} {{ $user->lname }}? This action cannot be undone.')"
                                            title="Delete User">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </form>
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

            <form method="POST" action="{{ route('user.update', $user->id) }}">
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
                    <button type="submit" class="btn btn-primary">Update User</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

@stop