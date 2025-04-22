<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Article;
use App\Models\PurchaseHistory;
use App\Models\SellAccount; // Assuming you have this model
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    public function index()
    {
        // Get count of all regular users
        $totalUsers = User::where('role', 'user')->count();
        
        // Get count of all admins
        $totalAdmins = User::where('role', 'admin')->count();
        
        // Get all articles with count
        $totalArticles = Article::count();
        $articles = Article::with('user')->get();
        
        // Get count of all sell accounts
        $totalAccounts = SellAccount::count();
        $accounts = SellAccount::all();
        
        // Get all purchase histories with related data
        $purchaseHistories = PurchaseHistory::with(['user', 'sellAccount'])->get();
        $totalPurchases = $purchaseHistories->count();

        return response()->json([
            'total_users' => $totalUsers,
            'total_admins' => $totalAdmins,
            'total_articles' => $totalArticles,
            'articles' => $articles,
            'total_accounts' => $totalAccounts,
            'accounts' => $accounts,
            'purchase_histories' => $purchaseHistories,
            'total_purchases' => $totalPurchases,
        ]);
    }
}