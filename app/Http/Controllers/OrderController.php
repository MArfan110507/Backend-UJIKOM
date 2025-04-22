<?php

namespace App\Http\Controllers;

use App\Models\Orders;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    // Menampilkan semua order milik user yang login
    public function index()
    {
        $orders = Orders::with(['items.sellaccount', 'payment', 'user'])
            ->where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($orders);
    }

    // Menampilkan detail order tertentu milik user
    public function show($id)
    {
        $order = Orders::with(['items.sellaccount', 'payment', 'user'])
            ->where('id', $id)
            ->where('user_id', auth()->id())
            ->first();

        if (!$order) {
            return response()->json(['message' => 'Order tidak ditemukan'], 404);
        }

        return response()->json($order);
    }

    // Membatalkan order jika masih pending
    public function cancel($id)
    {
        $order = Orders::where('id', $id)
            ->where('user_id', auth()->id())
            ->first();

        if (!$order) {
            return response()->json(['message' => 'Order tidak ditemukan'], 404);
        }

        if ($order->status !== 'pending') {
            return response()->json(['message' => 'Order tidak bisa dibatalkan'], 400);
        }

        $order->update(['status' => 'cancelled']);
        
        // Juga update status pembayaran jika ada
        if ($order->payment) {
            $order->payment->update(['status' => 'cancel']);
        }

        return response()->json(['message' => 'Order dibatalkan']);
    }
    
    // ===== ADMIN FUNCTIONS =====
    
    // Menampilkan semua order untuk admin
    public function adminIndex()
    {
        $this->authorize('admin');
        
        $orders = Orders::with(['items.sellaccount', 'payment', 'user'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($orders);
    }

    // Menampilkan detail order untuk admin
    public function adminShow($id)
    {
        $this->authorize('admin');
        
        $order = Orders::with(['items.sellaccount', 'payment', 'user'])
            ->where('id', $id)
            ->first();

        if (!$order) {
            return response()->json(['message' => 'Order tidak ditemukan'], 404);
        }

        return response()->json($order);
    }
}