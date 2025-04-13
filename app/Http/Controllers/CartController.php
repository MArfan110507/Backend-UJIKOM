<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\JualAkun;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    // Menampilkan semua item dalam keranjang pengguna
    public function index()
    {
        $carts = Cart::where('user_id', Auth::id())->with('sellaccount')->get();
        return response()->json($carts);
    }

    public function store(Request $request)
    {
        $request->validate([
            'sellaccount_id' => 'required|exists:sellaccounts,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $cart = Cart::updateOrCreate(
            [
                'user_id' => Auth::id(),
                'sellaccount_id' => $request->sellaccount_id,
            ],
            [
                'quantity' => $request->quantity,
            ]
        );

        return response()->json(['message' => 'Item added to cart', 'cart' => $cart], 201);
    }

    public function update(Request $request, $id)
    {
        $cart = Cart::where('user_id', Auth::id())->findOrFail($id);
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $cart->update(['quantity' => $request->quantity]);

        return response()->json(['message' => 'Cart updated', 'cart' => $cart]);
    }

    public function destroy($id)
    {
        $cart = Cart::where('user_id', Auth::id())->findOrFail($id);
        $cart->delete();

        return response()->json(['message' => 'Item removed from cart']);
    }
}

