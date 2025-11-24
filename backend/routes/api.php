<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\CryptoController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AlertController;
use App\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Routes publiques (sans authentification)
Route::post('auth/login', [AuthController::class, 'login']);
Route::post('auth/register', [AuthController::class, 'register']);
Route::get('cryptocurrencies', [CryptoController::class, 'index']);
Route::get('cryptocurrencies/{id}', [CryptoController::class, 'show']);
Route::get('cryptocurrencies/{id}/history', [CryptoController::class, 'history']);

// Routes protégées
Route::middleware('auth:sanctum,api')->group(function() {
    // Authentification
    Route::post('auth/logout', [AuthController::class, 'logout']);
    Route::get('auth/profile', [AuthController::class, 'profile']);
    Route::get('auth/me', [AuthController::class, 'profile']);
    Route::put('auth/profile', [AuthController::class, 'updateProfile']);
    
    // Wallet
    Route::get('wallet', [WalletController::class, 'index']);
    Route::post('buy', [WalletController::class, 'buy']);
    Route::post('sell', [WalletController::class, 'sell']);
    
    // Transactions
    Route::get('transactions', [TransactionController::class, 'index']);
    
    // Alerts
    Route::apiResource('alerts', AlertController::class);

    // Notifications
    Route::get('notifications', [NotificationController::class, 'index']);
    Route::put('notifications/{notification}/read', [NotificationController::class, 'markAsRead']);
    Route::put('notifications/read-all', [NotificationController::class, 'markAllAsRead']);
    Route::delete('notifications/{notification}', [NotificationController::class, 'destroy']);

    // Admin endpoints
    Route::middleware('admin')->prefix('admin')->group(function() {
        // Users Management
        Route::get('users', [AdminController::class, 'getUsers']);
        Route::post('users', [AdminController::class, 'storeUser']);
        Route::put('users/{id}', [AdminController::class, 'updateUser']);
        Route::delete('users/{id}', [AdminController::class, 'deleteUser']);
        
        // Stats
        Route::get('stats', [AdminController::class, 'getStats']);
    });
});

// Route par défaut pour les routes non définies
Route::fallback(function() {
    return response()->json([
        'status' => 'error',
        'message' => 'Route non trouvée.'
    ], 404);
});
