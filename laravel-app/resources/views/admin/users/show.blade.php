@extends('admin.layouts.admin')

@section('title', 'User Details')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">User Details</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <dl class="row">
                        <dt class="col-sm-4">ID</dt>
                        <dd class="col-sm-8">{{ $user->id }}</dd>

                        <dt class="col-sm-4">Name</dt>
                        <dd class="col-sm-8">{{ $user->name }}</dd>

                        <dt class="col-sm-4">Email</dt>
                        <dd class="col-sm-8">{{ $user->email }}</dd>

                        <dt class="col-sm-4">Roles</dt>
                        <dd class="col-sm-8">
                            @forelse($user->roles as $role)
                                <span class="badge bg-primary me-1 mb-1">{{ $role->name }}</span>
                            @empty
                                <span class="text-muted">No roles assigned</span>
                            @endforelse
                        </dd>

                        <dt class="col-sm-4">Permissions</dt>
                        <dd class="col-sm-8">
                            @forelse($user->permissions as $permission)
                                <span class="badge bg-secondary me-1 mb-1">{{ \App\Support\AdminAccess::label($permission->name) }}</span>
                            @empty
                                <span class="text-muted">No direct permissions assigned</span>
                            @endforelse
                        </dd>

                        <dt class="col-sm-4">Created At</dt>
                        <dd class="col-sm-8">{{ $user->created_at->format('Y-m-d H:i') }}</dd>
                    </dl>
                </div>
            </div>
        </div>
        <div class="card-footer">
            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Back to List</a>
            <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-warning">Edit User</a>
        </div>
    </div>
@endsection
