<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
});

// Admin-only routes. The role middleware enforces authorization on the server,
// so editing the role in the browser's localStorage cannot grant access here.
// Add real admin endpoints (products, orders, users…) inside this group.
Route::middleware(['auth:sanctum', 'role:admin|super admin'])->group(function () {
    Route::get('/admin/ping', fn () => response()->json(['ok' => true, 'scope' => 'admin']));
});
