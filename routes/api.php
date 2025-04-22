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
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\MidtransController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\OrderController;

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

    // Profile Routes
    Route::prefix('profile')->group(function () {
        // Profil user yang sedang login
        Route::get('/', [ProfileController::class, 'show']);
        Route::post('/', [ProfileController::class, 'update']);
        
        // Upload dan hapus foto
        Route::post('/photo', [ProfileController::class, 'uploadPhoto']);
        Route::delete('/photo', [ProfileController::class, 'deletePhoto']);
        
        // Untuk admin atau akses ke profil user lain
        Route::get('/{id}', [ProfileController::class, 'show']);
        Route::post('/{id}', [ProfileController::class, 'update']);
    });

    // Chat Routes
    Route::prefix('chats')->group(function () {
        Route::get('/{sellaccount_id}/{receiver_id}', [ChatController::class, 'index']); // Lihat chat
        Route::post('/', [ChatController::class, 'store']); // Kirim pesan baru
        Route::patch('/{id}/status', [ChatController::class, 'updateStatus']); // Update status chat
        Route::get('/', [ChatController::class, 'userChats']); // Ambil semua chat milik user
    });

    // Purchase History Routes
    Route::prefix('purchase-history')->group(function () {
        Route::get('/', [PurchaseHistoryController::class, 'history']); // Untuk user biasa
        Route::get('/admin', [PurchaseHistoryController::class, 'allHistory']); // Untuk admin
    });

    Route::post('midtrans/callback', [MidtransController::class, 'callback']);

    // User Cart Routes - MOVED FROM auth:sanctum TO jwt.auth MIDDLEWARE GROUP
    Route::get('cart', [CartController::class, 'viewCart']);
    Route::post('cart', [CartController::class, 'addToCart']);
    Route::put('cart/{id}', [CartController::class, 'updateCart']);
    Route::delete('cart/{id}', [CartController::class, 'removeFromCart']);
    
    // Checkout Route - MOVED FROM auth:sanctum TO jwt.auth MIDDLEWARE GROUP
    Route::post('checkout', [CheckoutController::class, 'checkout']);
    
    // User Orders Routes - MOVED FROM auth:sanctum TO jwt.auth MIDDLEWARE GROUP
    Route::get('orders', [OrderController::class, 'index']);
    Route::get('orders/{id}', [OrderController::class, 'show']);
    Route::post('orders/{id}/cancel', [OrderController::class, 'cancel']);
    
    // User Payment Routes - MOVED FROM auth:sanctum TO jwt.auth MIDDLEWARE GROUP
    Route::get('payments', [PaymentController::class, 'myPayments']);
    
    // Admin Routes (add middleware to restrict access) - MOVED FROM auth:sanctum TO jwt.auth MIDDLEWARE GROUP
    Route::middleware('can:admin')->prefix('admin')->group(function () {
        Route::get('orders', [OrderController::class, 'adminIndex']);
        Route::get('orders/{id}', [OrderController::class, 'adminShow']);
        Route::get('payments', [PaymentController::class, 'index']);
        Route::post('payments/{id}/approve', [PaymentController::class, 'approve']);
    });

    // SellAccount Routes (user and admin)
    Route::prefix('sellaccount')->group(function () {
        Route::get('/', [SellAccountController::class, 'index']);
        Route::get('/{id}', [SellAccountController::class, 'show']);
        Route::post('/', [SellAccountController::class, 'store']);
        Route::put('/{id}', [SellAccountController::class, 'update']);
        Route::delete('/{id}', [SellAccountController::class, 'destroy']);
    });

    // Article Routes (user and admin)
    Route::prefix('articles')->group(function () {
        Route::get('/', [ArticleController::class, 'apiIndex']); // GET /api/articles
        Route::post('/', [ArticleController::class, 'apistore']); // POST /api/articles
        Route::put('/toggle-status/{id}', [ArticleController::class, 'apiToggleStatus']); // PUT /api/articles/toggle-status/1
        Route::delete('/{id}', [ArticleController::class, 'apiDestroy']); // DELETE /api/articles/1
    });

    // Admin Dashboard (only for admin)
    Route::middleware('role:admin')->get('/admin/dashboard', [AdminDashboardController::class, 'index']);
});

// Remove or comment out this entire block since we moved all these routes to the jwt.auth middleware group
/* 
Route::middleware('auth:sanctum')->group(function () {
    // User Cart Routes
    Route::get('cart', [CartController::class, 'viewCart']);
    Route::post('cart', [CartController::class, 'addToCart']);
    Route::put('cart/{id}', [CartController::class, 'updateCart']);
    Route::delete('cart/{id}', [CartController::class, 'removeFromCart']);
    
    // Checkout Route
    Route::post('checkout', [CheckoutController::class, 'checkout']);
    
    // User Orders Routes
    Route::get('orders', [OrderController::class, 'index']);
    Route::get('orders/{id}', [OrderController::class, 'show']);
    Route::post('orders/{id}/cancel', [OrderController::class, 'cancel']);
    
    // User Payment Routes
    Route::get('payments', [PaymentController::class, 'myPayments']);
    
    // Admin Routes (add middleware to restrict access)
    Route::middleware('can:admin')->prefix('admin')->group(function () {
        Route::get('orders', [OrderController::class, 'adminIndex']);
        Route::get('orders/{id}', [OrderController::class, 'adminShow']);
        Route::get('payments', [PaymentController::class, 'index']);
        Route::post('payments/{id}/approve', [PaymentController::class, 'approve']);
    });
});
*/