@extends('admin.layouts.admin')

@section('title', 'Dashboard')

@section('content')
    <div class="row">
        <div class="col-lg-4 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $usersCount }}</h3>
                    <p>Products</p>
                </div>
                <div class="icon"><i class="fas fa-users"></i></div>
                <a href="{{ route('admin.users.index') }}" class="small-box-footer">View Products <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
         <div class="col-lg-4 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $usersCount }}</h3>
                    <p>Unread Emails</p>
                </div>
                <div class="icon"><i class="fas fa-users"></i></div>
                <a href="{{ route('admin.users.index') }}" class="small-box-footer">View Unread Emails <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
         <div class="col-lg-4 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $usersCount }}</h3>
                    <p>Registered Users</p>
                </div>
                <div class="icon"><i class="fas fa-users"></i></div>
                <a href="{{ route('admin.users.index') }}" class="small-box-footer">View Users <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
    </div>
@endsection
