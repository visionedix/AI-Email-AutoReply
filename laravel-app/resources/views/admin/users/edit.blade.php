@extends('admin.layouts.admin')

@section('title', 'Edit User')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Edit User</h3>
        </div>
        <form action="{{ route('admin.users.update', $user) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="card-body">
                <div class="mb-3">
                    <label for="name">Name</label>
                    <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                </div>
                <div class="mb-3">
                    <label for="email">Email</label>
                    <input type="email" name="email" id="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                </div>
                <div class="mb-3">
                    <label for="password">Password <small class="text-muted">(leave blank to keep current password)</small></label>
                    <input type="password" name="password" id="password" class="form-control">
                </div>
                <div class="mb-3">
                    <label for="roles">Roles</label>
                    <select name="roles[]" id="roles" class="form-select" multiple>
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}" @selected(in_array($role->id, old('roles', $user->roles->pluck('id')->all())))>
                                {{ $role->name }}
                            </option>
                        @endforeach
                    </select>
                    <small class="text-muted">Hold Ctrl or Cmd to pick multiple roles.</small>
                </div>
                <div class="mb-3">
                    <label for="permissions">Direct Permissions</label>
                    <select name="permissions[]" id="permissions" class="form-select" multiple>
                        @foreach($permissions as $permission)
                            <option value="{{ $permission->id }}" @selected(in_array($permission->id, old('permissions', $user->permissions->pluck('id')->all())))>
                                {{ \App\Support\AdminAccess::label($permission->name) }}
                            </option>
                        @endforeach
                    </select>
                    <small class="text-muted">Direct permissions are useful for one-off access.</small>
                </div>
            </div>
            <div class="card-footer">
                <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-success">Save Changes</button>
            </div>
        </form>
    </div>
@endsection
