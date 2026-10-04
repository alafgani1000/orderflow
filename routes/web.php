<?php

use App\Http\Controllers\BillingController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CustomerImportController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DemoDataController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderFileController;
use App\Http\Controllers\OrderImportController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PublicQuotationController;
use App\Http\Controllers\QuotationController;
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

Route::view('/terms', 'legal.terms')->name('terms');
Route::view('/privacy', 'legal.privacy')->name('privacy');

// Monitoring & Health Check
Route::get('/health', \App\Http\Controllers\HealthCheckController::class)->name('health');

// Public Tracking Route (tanpa login untuk pelanggan WhatsApp, rate limited 60 req/min)
Route::get('/lacak/{token}', [TrackingController::class, 'show'])
    ->middleware('throttle:60,1')
    ->name('orders.track');

// Public Quotation Route (tanpa login untuk pelanggan, rate limited 60 req/min)
Route::get('/penawaran/{token}', [PublicQuotationController::class, 'show'])
    ->middleware('throttle:60,1')
    ->name('quotations.public');
Route::post('/penawaran/{token}/approve', [PublicQuotationController::class, 'approve'])
    ->middleware('throttle:30,1')
    ->name('quotations.public.approve');
Route::post('/penawaran/{token}/reject', [PublicQuotationController::class, 'reject'])
    ->middleware('throttle:30,1')
    ->name('quotations.public.reject');

Route::post('/language/{locale}', [LocaleController::class, 'update'])
    ->whereIn('locale', array_keys(config('app.available_locales')))
    ->name('locale.update');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('onboarding', [OnboardingController::class, 'show'])->name('onboarding.show');
    Route::put('onboarding', [OnboardingController::class, 'update'])->name('onboarding.update');

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/demo-data', [DemoDataController::class, 'store'])->name('demo-data.store');
    Route::delete('/demo-data', [DemoDataController::class, 'destroy'])->name('demo-data.destroy');

    // Customers
    Route::get('customers/import', [CustomerImportController::class, 'create'])->name('customers.import.create');
    Route::post('customers/import/preview', [CustomerImportController::class, 'preview'])->name('customers.import.preview');
    Route::post('customers/import', [CustomerImportController::class, 'store'])->name('customers.import.store');
    Route::get('customers/import/template', [CustomerImportController::class, 'template'])->name('customers.import.template');
    Route::resource('customers', CustomerController::class);

    // Orders
    Route::get('orders/import', [OrderImportController::class, 'create'])->name('orders.import.create');
    Route::post('orders/import/preview', [OrderImportController::class, 'preview'])->name('orders.import.preview');
    Route::post('orders/import', [OrderImportController::class, 'store'])->name('orders.import.store');
    Route::get('orders/import/template', [OrderImportController::class, 'template'])->name('orders.import.template');
    Route::get('orders/kanban', [OrderController::class, 'kanban'])->name('orders.kanban');
    Route::get('orders/{order}/invoice', [OrderController::class, 'invoice'])->name('orders.invoice');
    Route::patch('orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.update-status');
    Route::resource('orders', OrderController::class);

    // Quotations (Penawaran Harga)
    Route::post('quotations/{quotation}/convert', [QuotationController::class, 'convertToOrder'])->name('quotations.convert');
    Route::patch('quotations/{quotation}/mark-as-sent', [QuotationController::class, 'markAsSent'])->name('quotations.mark-as-sent');
    Route::resource('quotations', QuotationController::class);

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
    Route::get('billing/invoices/{invoice}/proof', [BillingController::class, 'proof'])->name('billing.invoices.proof');

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
    Route::get('payments/{invoice}/proof', [SuperAdmin\PaymentController::class, 'proof'])->name('payments.proof');
    Route::post('payments/{invoice}/approve', [SuperAdmin\PaymentController::class, 'approve'])->name('payments.approve');
    Route::post('payments/{invoice}/reject', [SuperAdmin\PaymentController::class, 'reject'])->name('payments.reject');
});

require __DIR__.'/auth.php';
