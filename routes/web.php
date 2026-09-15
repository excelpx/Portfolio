<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PortfolioController;
use App\Http\Controllers\ImageKitController;

// =========================
// WEBSITE PORTFOLIO
// =========================

// Halaman utama website portfolio
Route::get('/', [PortfolioController::class, 'index']);

// Halaman portfolio
Route::get('/portfolio', [PortfolioController::class, 'index']);

// Menambahkan project
Route::post('/portfolio', [PortfolioController::class, 'store']);

// =========================
// CONTACT
// =========================

Route::post('/contact', [PortfolioController::class, 'sendContact'])
    ->name('contact.send');

// =========================
// IMAGEKIT
// =========================

// Generate authentication untuk Direct Upload ImageKit
Route::get('/imagekit/auth', [ImageKitController::class, 'auth'])
    ->middleware('auth')
    ->name('imagekit.auth');