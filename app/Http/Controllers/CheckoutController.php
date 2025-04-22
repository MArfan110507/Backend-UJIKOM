<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\OrderItems;
use App\Models\Orders;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Midtrans\Snap;
use Midtrans\Config;

class CheckoutController extends Controller
{
    public function __construct()
    {
        // Konfigurasi Midtrans
        Config::$serverKey = config('midtrans.server_key');
        Config::$clientKey = config('midtrans.client_key');
        Config::$isProduction = config('midtrans.is_production', false);
        Config::$isSanitized = true;
        Config::$is3ds = true;
    }

    public function checkout(Request $request)
{
    $user = auth()->user();

    $carts = collect(); // inisialisasi kosong

    // Jika request mengirim langsung sellaccount_id dan quantity
    if ($request->has('sellaccount_id') && $request->has('quantity')) {
        $sellaccount = \App\Models\SellAccount::find($request->sellaccount_id);
        if (!$sellaccount) {
            return response()->json(['message' => 'Akun jual tidak ditemukan'], 404);
        }

        $carts->push((object)[
            'sellaccount_id' => $sellaccount->id,
            'quantity' => $request->quantity,
            'sellaccount' => $sellaccount
        ]);
    } else {
        // Default ambil dari keranjang
        $carts = Cart::with('sellaccount')->where('user_id', $user->id)->get();

        if ($carts->isEmpty()) {
            return response()->json(['message' => 'Keranjang kosong'], 400);
        }
    }

    // Hitung total
    $total = $carts->sum(function ($cart) {
        return $cart->sellaccount->price * $cart->quantity;
    });

    DB::beginTransaction();

    try {
        // Buat order
        $order = Orders::create([
            'user_id' => $user->id,
            'status' => 'pending',
            'total_price' => $total,
        ]);

        // Simpan detail item
        foreach ($carts as $cart) {
            OrderItems::create([
                'order_id' => $order->id,
                'sellaccount_id' => $cart->sellaccount_id,
                'price' => $cart->sellaccount->price,
                'quantity' => $cart->quantity,
                'subtotal' => $cart->sellaccount->price * $cart->quantity,
            ]);
        }

        // Hapus keranjang (kalau checkout dari keranjang)
        if (!$request->has('sellaccount_id')) {
            Cart::where('user_id', $user->id)->delete();
        }

        // Buat Snap Token Midtrans
        $snapToken = Snap::getSnapToken([
            'transaction_details' => [
                'order_id' => 'ORDER-' . $order->id,
                'gross_amount' => $order->total_price,
            ],
            'customer_details' => [
                'first_name' => $user->name,
                'email' => $user->email,
            ],
        ]);

        if (!$snapToken) {
            throw new \Exception('Gagal mendapatkan Snap Token dari Midtrans');
        }
        
        // Simpan data pembayaran
        Payment::create([
            'order_id' => $order->id,
            'payment_method' => 'midtrans',
            'status' => 'pending',
            'snap_token' => $snapToken,
        ]);

        DB::commit();

        return response()->json([
            'message' => 'Checkout berhasil',
            'order' => $order,
            'snap_token' => $snapToken,
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'message' => 'Checkout gagal',
            'error' => $e->getMessage(),
        ], 500);
    }
}
}