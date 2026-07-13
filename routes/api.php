<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
});

// Public product catalog — anyone can browse the storefront.
Route::prefix('products')->group(function () {
    Route::get('/', [ProductController::class, 'index']);
    Route::get('/{product}', [ProductController::class, 'show']);
});

// Admin-only routes. The role middleware enforces authorization on the server,
// so editing the role in the browser's localStorage cannot grant access here.
// Add real admin endpoints (products, orders, users…) inside this group.
Route::middleware(['auth:sanctum', 'role:admin|super admin'])->group(function () {
    Route::get('/admin/ping', fn () => response()->json(['ok' => true, 'scope' => 'admin']));

    Route::prefix('products')->group(function () {
        Route::post('/', [ProductController::class, 'store']);
        Route::put('/{product}', [ProductController::class, 'update']);
        Route::delete('/{product}', [ProductController::class, 'destroy']);
    });
});
