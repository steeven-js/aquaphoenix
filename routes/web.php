<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\MonthPdfController;
use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/admin');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('/order/month/{month}/{year}/print', [MonthPdfController::class, 'generatePdf'])
    ->name('order.month.print')
    ->middleware(['auth']);

Route::get('/bon-livraison/{order}', [OrderController::class, 'downloadDeliveryNote'])
    ->name('order.delivery-note.download')
    ->middleware(['auth']);

require __DIR__.'/auth.php';
