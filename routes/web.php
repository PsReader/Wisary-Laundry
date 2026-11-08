<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportExportController;
use App\Http\Controllers\StaffAccountController;
use App\Http\Controllers\StaffPasswordResetController;
use App\Livewire\AuditLogs\Index as AuditLogsIndex;
use App\Livewire\Orders\Create;
use App\Livewire\Orders\Index;
use App\Livewire\Orders\Show;
use App\Livewire\Reports\Index as ReportsIndex;
use App\Models\Customer;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('dashboard'));

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', fn () => view('dashboard'))->name('dashboard');

    Route::get('/orders', Index::class)->name('orders');
    Route::get('/orders/create', Create::class)->name('orders.create');

    Route::middleware('super.admin')->group(function () {
        Route::get('/reports', ReportsIndex::class)->name('reports');
        Route::get('/reports/export/orders', [ReportExportController::class, 'orders'])->name('reports.export.orders');
        Route::get('/reports/export/payments', [ReportExportController::class, 'payments'])->name('reports.export.payments');
        Route::get('/reports/export/customers', [ReportExportController::class, 'customers'])->name('reports.export.customers');
        Route::get('/audit-logs', AuditLogsIndex::class)->name('audit-logs');
    });

    Route::get('/orders/{order}', Show::class)->name('orders.show');

    Route::get('/customers', fn () => view('customers.index'))->name('customers');
    Route::get('/customers/{customer}', fn (Customer $customer) => view('customers.show', ['customer' => $customer]))->name('customers.show');
    Route::get('/services', fn () => view('services.index'))->name('services');

    Route::middleware('super.admin')->group(function () {
        Route::get('/staff', fn () => view('staff.index'))->name('staff');
        Route::post('/staff/store', [StaffAccountController::class, 'store'])
            ->name('staff.store');
        Route::post('/staff/toggle-active', [StaffAccountController::class, 'toggleActive'])
            ->name('staff.toggle-active');
        Route::post('/staff/reset-password', [StaffPasswordResetController::class, 'store'])
            ->name('staff.password-reset');
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
