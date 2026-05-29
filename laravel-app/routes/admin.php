<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EmailTemplateController;
use App\Http\Controllers\Admin\MailController;
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
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('email-templates', EmailTemplateController::class)->except(['show']);
    Route::get('email-templates/{emailTemplate}', [EmailTemplateController::class, 'show'])->name('email-templates.show');
    Route::resource('products', ProductController::class)->except(['show']);
    Route::get('products/{product}', [ProductController::class, 'show'])->name('products.show');
    Route::resource('users', UserController::class)->except(['show']);
    Route::get('users/{user}', [UserController::class, 'show'])->name('users.show');

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
