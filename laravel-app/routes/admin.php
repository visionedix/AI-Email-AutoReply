<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\AccessControlController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EmailTemplateController;
use App\Http\Controllers\Admin\MailController;
use App\Http\Controllers\Admin\QuotationController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::redirect('/admin', '/admin/dashboard');
Route::redirect('/login', '/admin/login')->name('login');

Route::middleware('guest')->group(function () {
    Route::get('/admin/login', [AuthController::class, 'showLoginForm'])->name('admin.login');
    Route::post('/admin/login', [AuthController::class, 'login'])->name('admin.login.submit');
});

Route::post('/admin/logout', [AuthController::class, 'logout'])->middleware('auth')->name('admin.logout');

Route::prefix('admin')->middleware('auth')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->middleware('permission:' . \App\Support\AdminAccess::VIEW_DASHBOARD)
        ->name('dashboard');

    Route::middleware('permission:' . \App\Support\AdminAccess::MANAGE_USERS)->group(function () {
        Route::resource('users', UserController::class)->except(['show']);
        Route::get('users/{user}', [UserController::class, 'show'])->name('users.show');
    });

    Route::middleware('permission:' . \App\Support\AdminAccess::MANAGE_PRODUCTS)->group(function () {
        Route::resource('products', ProductController::class)->except(['show']);
        Route::get('products/{product}', [ProductController::class, 'show'])->name('products.show');
    });

    Route::middleware('permission:' . \App\Support\AdminAccess::MANAGE_EMAIL_TEMPLATES)->group(function () {
        Route::resource('email-templates', EmailTemplateController::class)->except(['show']);
        Route::get('email-templates/{emailTemplate}', [EmailTemplateController::class, 'show'])->name('email-templates.show');
    });

    Route::middleware('permission:' . \App\Support\AdminAccess::MANAGE_QUOTATIONS)->group(function () {
        Route::get('quotations', [QuotationController::class, 'index'])->name('quotations.index');
        Route::post('quotations', [QuotationController::class, 'store'])->name('quotations.store');
        Route::get('quotations/{quotation}', [QuotationController::class, 'show'])->name('quotations.show');
        Route::get('quotations/{quotation}/edit', [QuotationController::class, 'edit'])->name('quotations.edit');
        Route::put('quotations/{quotation}', [QuotationController::class, 'update'])->name('quotations.update');
        Route::patch('quotations/{quotation}', [QuotationController::class, 'update']);
        Route::delete('quotations/{quotation}', [QuotationController::class, 'destroy'])->name('quotations.destroy');
    });

    Route::middleware('permission:' . \App\Support\AdminAccess::MANAGE_MAIL)->group(function () {
        Route::get('mail/messages', [MailController::class, 'inbox'])->name('mail.messages.index');
        Route::get('mail/unread', [MailController::class, 'inbox'])->name('mail.messages.unread');
        Route::get('mail/messages/{messageId}', [MailController::class, 'show'])->name('mail.messages.show');
        Route::patch('mail/messages/{messageId}/read', [MailController::class, 'markAsRead'])->name('mail.messages.read');
        Route::post('mail/send', [MailController::class, 'send'])->name('mail.send');

        Route::get('outlook/messages', [MailController::class, 'inbox'])->name('outlook.messages.index');
        Route::get('outlook/unread', [MailController::class, 'inbox'])->name('outlook.messages.unread');
        Route::get('outlook/messages/{messageId}', [MailController::class, 'show'])->name('outlook.messages.show');
        Route::patch('outlook/messages/{messageId}/read', [MailController::class, 'markAsRead'])->name('outlook.messages.read');
        Route::post('outlook/send', [MailController::class, 'send'])->name('outlook.send');
    });

    Route::prefix('access-control')
        ->middleware('permission:' . \App\Support\AdminAccess::MANAGE_ACCESS_CONTROL)
        ->controller(AccessControlController::class)
        ->name('access-control.')
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/permissions', 'storePermission')->name('permissions.store');
            Route::delete('/permissions/{permission}', 'destroyPermission')->name('permissions.destroy');
            Route::post('/roles', 'storeRole')->name('roles.store');
            Route::put('/roles/{role}', 'updateRole')->name('roles.update');
            Route::delete('/roles/{role}', 'destroyRole')->name('roles.destroy');
        });
});
