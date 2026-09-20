<?php

use App\Http\Controllers\BillingController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderFileController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\SuperAdmin;
use App\Http\Controllers\TrackingController;
use Illuminate\Support\Facades\Route;

// Public SaaS Landing Page (jika sudah login, langsung ke dashboard)
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return view('landing');
})->name('home');

// Public Tracking Route (tanpa login untuk pelanggan WhatsApp, rate limited 60 req/min)
Route::get('/lacak/{token}', [TrackingController::class, 'show'])
    ->middleware('throttle:60,1')
    ->name('orders.track');

Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Customers
    Route::resource('customers', CustomerController::class);

    // Orders
    Route::get('orders/kanban', [OrderController::class, 'kanban'])->name('orders.kanban');
    Route::get('orders/{order}/invoice', [OrderController::class, 'invoice'])->name('orders.invoice');
    Route::patch('orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.update-status');
    Route::resource('orders', OrderController::class);

    // Payments
    Route::get('payments', [PaymentController::class, 'index'])->name('payments.index');
    Route::post('orders/{order}/payments', [PaymentController::class, 'store'])->name('orders.payments.store');
    Route::delete('orders/{order}/payments/{payment}', [PaymentController::class, 'destroy'])->name('orders.payments.destroy');

    // Order Files
    Route::post('orders/{order}/files', [OrderFileController::class, 'store'])->name('orders.files.store');
    Route::get('orders/{order}/files/{file}/download', [OrderFileController::class, 'download'])->name('orders.files.download');
    Route::delete('orders/{order}/files/{file}', [OrderFileController::class, 'destroy'])->name('orders.files.destroy');

    // Reports (Keuangan & Export Excel)
    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('reports/export', [ReportController::class, 'export'])->name('reports.export');

    // Employees (Kelola Staf Toko)
    Route::post('employees', [EmployeeController::class, 'store'])->name('employees.store');
    Route::delete('employees/{employee}', [EmployeeController::class, 'destroy'])->name('employees.destroy');

    // Billing & Subscriptions (Paket Langganan Toko)
    Route::get('billing', [BillingController::class, 'index'])->name('billing.index');
    Route::get('billing/checkout/{plan}', [BillingController::class, 'checkout'])->name('billing.checkout');
    Route::post('billing/confirm', [BillingController::class, 'confirmPayment'])->name('billing.confirm');

    // Settings
    Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
    Route::put('settings/profile', [SettingController::class, 'updateProfile'])->name('settings.profile');
    Route::put('settings/password', [SettingController::class, 'updatePassword'])->name('settings.password');
});

// Super Admin Platform Area
Route::prefix('admin')->name('admin.')->middleware(['auth', 'superadmin'])->group(function () {
    Route::get('/', [SuperAdmin\DashboardController::class, 'index'])->name('dashboard');
    Route::get('tenants', [SuperAdmin\TenantController::class, 'index'])->name('tenants.index');
    Route::get('tenants/{tenant}', [SuperAdmin\TenantController::class, 'show'])->name('tenants.show');
    Route::post('tenants/{tenant}/subscription', [SuperAdmin\TenantController::class, 'updateSubscription'])->name('tenants.subscription');
    Route::get('payments', [SuperAdmin\PaymentController::class, 'index'])->name('payments.index');
    Route::post('payments/{invoice}/approve', [SuperAdmin\PaymentController::class, 'approve'])->name('payments.approve');
    Route::post('payments/{invoice}/reject', [SuperAdmin\PaymentController::class, 'reject'])->name('payments.reject');
});

require __DIR__.'/auth.php';
