<?php

namespace App\Http\Controllers\Admin;

use App\Admin\Models\Product;
use App\Http\Controllers\Controller;
use App\Admin\Models\User;
use App\Models\Quotation;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'usersCount' => User::count(),
            'productsCount' => Product::count(),
            'quotationsCount' => Quotation::count(),
            'draftQuotationsCount' => Quotation::query()->where('status', 'draft')->count(),
            'sentQuotationsCount' => Quotation::query()->where('status', 'sent')->count(),
            'failedQuotationsCount' => Quotation::query()->where('status', 'failed')->count(),
            'todayQuotationsCount' => Quotation::query()->whereDate('created_at', today())->count(),
        ];

        $recentQuotations = Quotation::query()
            ->with('product')
            ->latest('id')
            ->limit(5)
            ->get();

        return view('admin.dashboard', [
            'stats' => $stats,
            'recentQuotations' => $recentQuotations,
        ]);
    }
}
