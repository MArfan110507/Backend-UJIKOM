<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SellAccountController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\API\SocialAuthController;
use App\Http\Controllers\FAQController;
use App\Http\Controllers\FAQChatController;
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
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::put('/profile', [ProfileController::class, 'update']);
    Route::put('/profile/password', [ProfileController::class, 'updatePassword']);

    // FAQ
    Route::get('/faqs', [FAQController::class, 'index']);
    Route::post('/faqs', [FAQController::class, 'store']);
    Route::put('/faqs/{id}/close', [FAQController::class, 'close']);

    // FAQ Chat
    Route::get('/faqs/{faq_id}/chats', [FAQChatController::class, 'index']);
    Route::post('/faqs/{faq_id}/chats', [FAQChatController::class, 'store']);

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
    Route::post('/midtrans/token', [TransactionController::class, 'createMidtransToken']);
    Route::get('/transactions', [TransactionController::class, 'index']);
    Route::post('/transactions', [TransactionController::class, 'store']);
    Route::get('/transactions/{id}', [TransactionController::class, 'show']);
    Route::put('/transactions/{id}/status', [TransactionController::class, 'updateStatus']);

    Route::prefix('sellaccount')->group(function () {
        Route::get('/', [SellAccountController::class, 'index']);
        Route::post('/', [SellAccountController::class, 'store']);
        Route::get('/{id}', [SellAccountController::class, 'show']);
        Route::put('/{id}', [SellAccountController::class, 'update']);
        Route::delete('/{id}', [SellAccountController::class, 'destroy']);
    });

    // Article
    Route::prefix('articles')->group(function () {
        Route::get('/', [ArticleController::class, 'apiIndex']); // GET /api/articles
        Route::post('/', [ArticleController::class, 'store']); // POST /api/articles
        Route::put('/toggle-status/{id}', [ArticleController::class, 'apiToggleStatus']); // PUT /api/articles/toggle-status/1
        Route::delete('/{id}', [ArticleController::class, 'apiDestroy']); // DELETE /api/articles/1
    });    
});
