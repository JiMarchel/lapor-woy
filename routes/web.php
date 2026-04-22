<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\TicketReplyController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::inertia('/', 'Welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::middleware(['auth'])->group(function () {
    Route::resource('tickets.replies', TicketReplyController::class)->only(['index', 'store']);

    Route::middleware('role:user')->group(function () {
        Route::resource('tickets', TicketController::class)->only(['index', 'store', 'update']);
        Route::get('tickets/arsip', [TicketController::class, 'arsip'])->name('tickets.arsip');
    });

    Route::middleware('role:admin')->group(function () {
        Route::resource('admin/tickets', AdminController::class)->names('admin');
        Route::get('admin/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
        Route::get('admin/arsip', [AdminController::class, 'arsip'])->name('admin.arsip');
    });
});

require __DIR__.'/settings.php';
