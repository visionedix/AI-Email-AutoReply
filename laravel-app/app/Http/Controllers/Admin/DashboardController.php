<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Admin\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $usersCount = User::count();

        return view('admin.dashboard', compact('usersCount'));
    }
}
