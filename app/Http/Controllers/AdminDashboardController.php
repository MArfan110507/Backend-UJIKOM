<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Article;
use App\Models\PurchaseHistory;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    public function index()
    {
        // Ambil semua user biasa (role user)
        $users = User::where('role', 'user')->get();

        // Ambil semua artikel yang dibuat oleh admin (berdasarkan role dan relasi user_id)
        $adminArticles = Article::with('user')
            ->whereHas('user', function ($query) {
                $query->where('role', 'admin');
            })->get();

        // Ambil semua history pembelian beserta relasi user dan akun jual
        $purchaseHistories = PurchaseHistory::with(['user', 'sellAccount'])->get();

        return response()->json([
            'users' => $users,
            'admin_articles' => $adminArticles,
            'purchase_histories' => $purchaseHistories,
        ]);
    }
}
