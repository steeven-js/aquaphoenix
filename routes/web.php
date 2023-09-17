<?php

use App\Http\Livewire\Form;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Redirect;
use App\Http\Controllers\MonthController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PrintController;

\Illuminate\Support\Facades\Route::get('form', Form::class);

Route::middleware('auth')->group(function () {
    Route::get('/print/order/{id}', [PrintController::class, 'printOrder'])->name('order.print');
    Route::get('/email/livraison-mail/{order}', [OrderController::class, 'livraisonMail'])->name('livraison.mail');

    Route::get('/test/month', [MonthController::class, 'month'])->name('month.test');
});
