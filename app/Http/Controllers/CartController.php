<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cart;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    // Tambahkan akun ke keranjang
    public function addToCart(Request $request)
    {
        // Validasi input
        $validated = $request->validate([
            'sellaccount_id' => 'required|exists:sellaccounts,id',
            'quantity' => 'required|integer|min:1'
        ]);

        // Update atau create isi cart
        $cart = Cart::updateOrCreate(
            [
                'user_id' => auth()->id(),
                'sellaccount_id' => $validated['sellaccount_id'],
            ],
            [
                'quantity' => $validated['quantity'],
            ]
        );

        return response()->json([
            'message' => 'Ditambahkan ke keranjang',
            'cart' => $cart
        ]);
    }

    // Menampilkan isi keranjang user
    public function viewCart()
    {
        $carts = Cart::with('sellaccount')
            ->where('user_id', auth()->id())
            ->get();

        return response()->json($carts);
    }

    // Update quantity item dalam cart
    public function updateCart(Request $request, $id)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1'
        ]);

        $cart = Cart::where('id', $id)
            ->where('user_id', auth()->id())
            ->first();

        if (!$cart) {
            return response()->json(['message' => 'Item tidak ditemukan'], 404);
        }

        $cart->quantity = $validated['quantity'];
        $cart->save();

        return response()->json([
            'message' => 'Cart updated',
            'cart' => $cart
        ]);
    }

    // Menghapus item dari cart
    public function removeFromCart($id)
    {
        $cart = Cart::where('id', $id)
            ->where('user_id', auth()->id())
            ->first();

        if (!$cart) {
            return response()->json(['message' => 'Item tidak ditemukan'], 404);
        }

        $cart->delete();

        return response()->json(['message' => 'Item dihapus dari keranjang']);
    }
}