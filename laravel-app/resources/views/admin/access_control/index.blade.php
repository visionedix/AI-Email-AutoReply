@extends('admin.layouts.admin')

@section('title', 'Roles & Permissions')

@section('content')
    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header">
                    <h3 class="card-title">Create Permission</h3>
                </div>
                <form action="{{ url('/admin/access-control/permissions') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="permission_name" class="form-label">Permission Name</label>
                            <input
                                type="text"
                                name="permission_name"
                                id="permission_name"
                                class="form-control"
                                value="{{ old('permission_name') }}"
                                placeholder="manage-custom-reports"
                                required
                            >
                            <small class="text-muted">Use lowercase letters and hyphens only.</small>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">Add Permission</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card h-100">
                <div class="card-header">
                    <h3 class="card-title">Create Role</h3>
                </div>
                <form action="{{ url('/admin/access-control/roles') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="role_name" class="form-label">Role Name</label>
                                <input
                                    type="text"
                                    name="role_name"
                                    id="role_name"
                                    class="form-control"
                                    value="{{ old('role_name') }}"
                                    placeholder="support"
                                    required
                                >
                            </div>
                            <div class="col-md-8">
                                <label for="role_permissions" class="form-label">Permissions</label>
                                <select name="permissions[]" id="role_permissions" class="form-select" multiple>
                                    @foreach($permissions as $permission)
                                        <option value="{{ $permission->id }}">{{ \App\Support\AdminAccess::label($permission->name) }}</option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Pick the modules this role can access.</small>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-success">Create Role</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Permissions</h3>
                    <span class="badge bg-secondary">{{ $permissions->count() }}</span>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Roles</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($permissions as $permission)
                                <tr>
                                    <td>{{ \App\Support\AdminAccess::label($permission->name) }}</td>
                                    <td><span class="badge bg-info">{{ $permission->roles_count }}</span></td>
                                    <td class="text-end">
                                        <form action="{{ url('/admin/access-control/permissions/'.$permission->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this permission?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted">No permissions found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Roles</h3>
                    <span class="badge bg-secondary">{{ $roles->count() }}</span>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th style="width: 22%">Role</th>
                                <th>Permissions</th>
                                <th>Users</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($roles as $role)
                                <tr>
                                    <td>
                                        <input type="text" name="name" value="{{ old('name', $role->name) }}" class="form-control form-control-sm" form="role-update-{{ $role->id }}" required>
                                    </td>
                                    <td>
                                        <select name="permissions[]" class="form-select form-select-sm" multiple form="role-update-{{ $role->id }}">
                                            @php($selectedPermissions = old('permissions', $role->permissions->pluck('id')->all()))
                                            @foreach($permissions as $permission)
                                                <option value="{{ $permission->id }}" @selected(in_array($permission->id, $selectedPermissions))>
                                                    {{ \App\Support\AdminAccess::label($permission->name) }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary">{{ $role->users_count }}</span>
                                    </td>
                                    <td class="text-end">
                                        <form id="role-update-{{ $role->id }}" action="{{ url('/admin/access-control/roles/'.$role->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('PUT')
                                        </form>
                                        <button type="submit" form="role-update-{{ $role->id }}" class="btn btn-sm btn-success">Save</button>
                                        <form action="{{ url('/admin/access-control/roles/'.$role->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this role?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted">No roles found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Module Permission Map</h3>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @foreach(\App\Support\AdminAccess::modules() as $module)
                            <div class="col-md-6 col-xl-4">
                                <div class="border rounded-3 p-3 h-100">
                                    <div class="fw-semibold">{{ $module['label'] }}</div>
                                    <div class="text-muted small">{{ \App\Support\AdminAccess::label($module['permission']) }}</div>
                                    <div class="mt-2">
                                        <span class="badge bg-dark">{{ $module['permission'] }}</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
