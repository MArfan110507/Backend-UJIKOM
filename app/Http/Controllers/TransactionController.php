<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;
use Midtrans\Snap;
use Midtrans\Config;

class TransactionController extends Controller
{
    public function index()
    {
        return Transaction::with('user')->latest()->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'items' => 'required|array',
            'total_price' => 'required|numeric',
            'payment_method' => 'required|string',
            'payment_gateway' => 'nullable|string',
            'transaction_id' => 'nullable|string',
        ]);

        $transaction = Transaction::create([
            'user_id' => Auth::id(),
            'items' => $validated['items'],
            'total_price' => $validated['total_price'],
            'status' => 'pending',
            'payment_method' => $validated['payment_method'],
            'payment_gateway' => $validated['payment_gateway'] ?? null,
            'transaction_id' => $validated['transaction_id'] ?? null,
        ]);

        return response()->json([
            'message' => 'Transaction created successfully.',
            'transaction' => $transaction
        ]);
    }

    public function createMidtransToken(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'items' => 'required|array',
            'total_price' => 'required|numeric',
            'payment_method' => 'required|string',
        ]);

        $transaction = Transaction::create([
            'user_id' => $user->id,
            'items' => $validated['items'],
            'total_price' => $validated['total_price'],
            'status' => 'pending',
            'payment_method' => $validated['payment_method'],
            'payment_gateway' => 'midtrans',
        ]);

        // Midtrans config (bisa dilepas kalau sudah di AppServiceProvider)
        Config::$serverKey = env('MIDTRANS_SERVER_KEY');
        Config::$isProduction = false;
        Config::$isSanitized = true;
        Config::$is3ds = true;

        $order_id = 'TXN-' . $transaction->id . '-' . time();

        $params = [
            'transaction_details' => [
                'order_id' => $order_id,
                'gross_amount' => $transaction->total_price,
            ],
            'customer_details' => [
                'first_name' => $user->name,
                'email' => $user->email,
            ],
        ];

        $snapToken = Snap::getSnapToken($params);

        $transaction->update([
            'transaction_id' => $order_id,
        ]);

        return response()->json([
            'snap_token' => $snapToken,
            'transaction' => $transaction,
        ]);
    }

    public function show($id)
    {
        $transaction = Transaction::with('user')->findOrFail($id);
        return response()->json($transaction);
    }

    public function updateStatus(Request $request, $id)
    {
        $transaction = Transaction::findOrFail($id);
        $transaction->update([
            'status' => $request->status
        ]);

        return response()->json([
            'message' => 'Transaction status updated.',
            'transaction' => $transaction
        ]);
    }
}
