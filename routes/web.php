<?php

use App\Http\Controllers\AvailableController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KitController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UnavailableController;
use App\Http\Controllers\UndiscoveredController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::middleware('auth')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Suppliers (+ detail)
    Route::get('/suppliers', [SupplierController::class, 'index'])->name('suppliers.index');
    Route::get('/suppliers/export', [SupplierController::class, 'export'])->name('suppliers.export');
    Route::get('/suppliers/by-sku/{sku}', [SupplierController::class, 'showBySku'])->name('suppliers.by-sku');
    Route::get('/suppliers/{supplier}', [SupplierController::class, 'show'])->name('suppliers.show');

    // Availables
    Route::get('/availables', [AvailableController::class, 'index'])->name('availables.index');
    Route::get('/availables/export', [AvailableController::class, 'export'])->name('availables.export');

    // Unavailables
    Route::get('/unavailables', [UnavailableController::class, 'index'])->name('unavailables.index');
    Route::get('/unavailables/export', [UnavailableController::class, 'export'])->name('unavailables.export');

    // Undiscovereds
    Route::get('/undiscovereds', [UndiscoveredController::class, 'index'])->name('undiscovereds.index');
    Route::get('/undiscovereds/export', [UndiscoveredController::class, 'export'])->name('undiscovereds.export');

    // Kits
    Route::get('/kits', [KitController::class, 'index'])->name('kits.index');
    Route::get('/kits/export', [KitController::class, 'export'])->name('kits.export');

    // Profile (Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
