<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MonthController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Redirect;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::middleware('auth')->group(function () {

    Route::get('/', function () {
        return redirect('/admin');
    });

    Route::get('/dashboard', function () {
        return view('dashboard');
    })->middleware(['auth', 'verified'])->name('dashboard');

    Route::get('/print/order/print/{order}', [OrderController::class, 'openPdf'])->name('livraison.print');
    Route::get('/print/order/mail/{order}', [OrderController::class, 'mailLivraison'])->name('livraison.mail');
    Route::get('/print/month/{month}/{year}', [OrderController::class, 'ordersByMonth'])->name('order.month.print');

    Route::get('/test/month', [MonthController::class, 'month'])->name('month.test');
    Route::get('/test/updateOrderStatus', [OrderController::class, 'updateOrderStatus'])->name('order.satus.test');
    Route::get('/test/updateNumber', [OrderController::class, 'updateNumber'])->name('number');

    Route::get('/generate/generateAllPdfs', [OrderController::class, 'generateAllPdfs'])->name('generate.pdf');
    Route::get('/generate/generateAllOrdersByMonthPdfs', [OrderController::class, 'generateAllOrdersByMonthPdfs'])->name('generate.monthOrders.pdf');


    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
