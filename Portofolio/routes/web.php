<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PortfolioController;

// Rute untuk melihat data (READ)
Route::get('/portfolio', [PortfolioController::class, 'index']);

// Rute untuk menambah data (CREATE)
Route::post('/portfolio', [PortfolioController::class, 'store']);