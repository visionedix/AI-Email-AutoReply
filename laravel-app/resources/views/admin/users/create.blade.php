@extends('admin.layouts.admin')

@section('title', 'Create User')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">New User</h3>
        </div>
        <form action="{{ route('admin.users.store') }}" method="POST">
            @csrf
            <div class="card-body">
                <div class="mb-3">
                    <label for="name">Name</label>
                    <input type="text" name="name" id="name" class="form-control" value="{{ old('name') }}" required>
                </div>
                <div class="mb-3">
                    <label for="email">Email</label>
                    <input type="email" name="email" id="email" class="form-control" value="{{ old('email') }}" required>
                </div>
                <div class="mb-3">
                    <label for="password">Password</label>
                    <input type="password" name="password" id="password" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="roles">Roles</label>
                    <select name="roles[]" id="roles" class="form-select" multiple>
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}" @selected(in_array($role->id, old('roles', [])))>{{ $role->name }}</option>
                        @endforeach
                    </select>
                    <small class="text-muted">Hold Ctrl or Cmd to pick multiple roles.</small>
                </div>
                <div class="mb-3">
                    <label for="permissions">Direct Permissions</label>
                    <select name="permissions[]" id="permissions" class="form-select" multiple>
                        @foreach($permissions as $permission)
                            <option value="{{ $permission->id }}" @selected(in_array($permission->id, old('permissions', [])))>
                                {{ \App\Support\AdminAccess::label($permission->name) }}
                            </option>
                        @endforeach
                    </select>
                    <small class="text-muted">Direct permissions are useful for exceptions outside a role.</small>
                </div>
            </div>
            <div class="card-footer">
                <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Create</button>
            </div>
        </form>
    </div>
@endsection
