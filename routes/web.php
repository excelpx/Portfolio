<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PortfolioController;

// Halaman utama website portfolio
Route::get('/', [PortfolioController::class, 'index']);

// Halaman portfolio
Route::get('/portfolio', [PortfolioController::class, 'index']);

// Menambahkan project
Route::post('/portfolio', [PortfolioController::class, 'store']);

Route::post('/contact', [PortfolioController::class, 'sendContact'])->name('contact.send');