<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SellAccountController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\API\SocialAuthController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\PurchaseHistoryController;
use App\Http\Controllers\TransactionController;

// Basic test
Route::get('/test', function () {
    return response()->json([
        'message' => 'API Laravel Berhasil Terhubung dengan Postman!'
    ]);
});

// Auth Routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Google OAuth
Route::get('/auth/google', [SocialAuthController::class, 'redirectToGoogle']);
Route::get('/auth/google/callback', [SocialAuthController::class, 'handleGoogleCallback']);

// Routes that require JWT Authentication
Route::middleware(['jwt.auth'])->group(function () {

    // Authenticated User
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // User Info
    Route::get('/user', function (Request $request) {
        return response()->json(auth()->user());
    });

    // Profile
Route::middleware('auth:api')->prefix('profile')->group(function () {
    Route::get('/', [ProfileController::class, 'show']); // Menampilkan profil
    Route::put('/', [ProfileController::class, 'update']); // Mengubah nickname, avatar, dan password
    Route::post('/reset-password', [ProfileController::class, 'resetPassword']); // Reset password
});


    // Chats
    Route::middleware(['auth:api'])->group(function () {
        // Lihat semua chat berdasarkan sellaccount_id dan receiver_id
        Route::get('/chats/{sellaccount_id}/{receiver_id}', [ChatController::class, 'index']);

        // Kirim pesan baru
        Route::post('/chats', [ChatController::class, 'store']);

        // Update status chat (pending / accept)
        Route::patch('/chats/{id}/status', [ChatController::class, 'updateStatus']);
    });

    // Purchase History
    Route::prefix('purchase-history')->group(function () {
        Route::get('/', [PurchaseHistoryController::class, 'index']);
        Route::post('/', [PurchaseHistoryController::class, 'store']);
        Route::get('/{id}', [PurchaseHistoryController::class, 'show']);
    });

    // Cart (Keranjang)
    Route::prefix('cart')->group(function () {
        Route::get('/', [CartController::class, 'index']);
        Route::post('/', [CartController::class, 'store']);
        Route::put('/{id}', [CartController::class, 'update']);
        Route::delete('/{id}', [CartController::class, 'destroy']);
    });

    // Transactions

    Route::middleware('auth:api')->prefix('transaction')->group(function () {
        Route::get('/', [TransactionController::class, 'index']);
        Route::post('/', [TransactionController::class, 'store']);
        Route::post('/{id}/approve', [TransactionController::class, 'approve']);
        Route::post('/{id}/refund', [TransactionController::class, 'refund']);
        Route::post('/createMidtransToken', [TransactionController::class, 'createMidtransToken']);
        Route::get('/{id}', [TransactionController::class, 'show']);
        Route::put('/{id}/status', [TransactionController::class, 'updateStatus']);
    });
    


    Route::prefix('sellaccount')->group(function () {
        Route::get('/', [SellAccountController::class, 'index']);
        Route::get('/{id}', [SellAccountController::class, 'show']);
        Route::post('/', [SellAccountController::class, 'store']);
        Route::put('/{id}', [SellAccountController::class, 'update']);
        Route::delete('/{id}', [SellAccountController::class, 'destroy']);
    });


    // Article
    Route::prefix('articles')->group(function () {
        Route::get('/', [ArticleController::class, 'apiIndex']); // GET /api/articles
        Route::post('/', [ArticleController::class, 'apistore']); // POST /api/articles
        Route::put('/toggle-status/{id}', [ArticleController::class, 'apiToggleStatus']); // PUT /api/articles/toggle-status/1
        Route::delete('/{id}', [ArticleController::class, 'apiDestroy']); // DELETE /api/articles/1
    });
});
