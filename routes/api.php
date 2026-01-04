<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\RentalController;
use App\Http\Controllers\Api\ProductController;

// Public Routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// MIDTRANS CALLBACK (Webhook)
Route::post('/midtrans-callback', [\App\Http\Controllers\Api\CallbackController::class, 'midtransWebhook']);

// Homepage / Search tidak butuh login
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{slug}', [ProductController::class, 'show']);

// Protected Routes (Butuh Token)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // Product Management (Lender)
    Route::post('/products', [ProductController::class, 'store']);
    Route::put('/products/{id}', [ProductController::class, 'update']);
    Route::delete('/products/{id}', [ProductController::class, 'destroy']);

    // Rental / Booking System
    Route::post('/rentals', [RentalController::class, 'store']); // Checkout
    Route::get('/rentals', [RentalController::class, 'index']);      // History Saya (Renter)
    Route::get('/rentals/{id}', [RentalController::class, 'show']);  // Detail Transaksi

    // Lender Dashboard
    Route::get('/lender/orders', [RentalController::class, 'lenderOrders']); // Pesanan Masuk
    Route::post('/rentals/{id}/return', [RentalController::class, 'returnProduct']); // Endpoint Return
});
