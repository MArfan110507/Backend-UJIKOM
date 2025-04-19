<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;
use Midtrans\Snap;
use Midtrans\Config;
use Midtrans\Transaction as MidtransTransaction;
use App\Models\PurchaseHistory;

class TransactionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

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

        // Midtrans config
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
        $transaction = Transaction::with('user')->find($id);

        if (!$transaction) {
            return response()->json(['message' => 'Transaction not found'], 404);
        }

        return response()->json($transaction);
    }


    public function refund(Request $request, $id)
    {
        $transaction = Transaction::find($id);

        if (!$transaction) {
            return response()->json(['message' => 'Transaction not found'], 404);
        }

        if ($transaction->payment_gateway !== 'midtrans') {
            return response()->json(['error' => 'Only Midtrans transactions can be refunded.'], 400);
        }

        // Konfigurasi Midtrans
        Config::$serverKey = env('MIDTRANS_SERVER_KEY');
        Config::$isProduction = false; // ubah ke true jika sudah live

        $midtransTransactionId = $transaction->transaction_id;

        try {
            $refundResponse = MidtransTransaction::refund($midtransTransactionId, [
                'refund_key' => 'refund-' . time(),
                'amount' => $transaction->total_price,
                'reason' => 'Customer canceled the order'
            ]);

            $transaction->update([
                'status' => 'refunded'
            ]);

            return response()->json([
                'message' => 'Refund successful.',
                'refund_response' => $refundResponse,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Refund failed.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function listPending()
    {
        if (auth()->user()->role !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $transactions = Transaction::where('status', 'pending')->with('user:id,name')->get();

        return response()->json($transactions);
    }

    public function approve($id)
    {
        $transaction = Transaction::findOrFail($id);
    
        if (auth()->user()->role !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
    
        if ($transaction->status !== 'pending') {
            return response()->json(['error' => 'Only pending transactions can be approved.'], 422);
        }
    
        $transaction->status = 'complete';
        $transaction->save();
    
        // Simpan ke history pembelian
        foreach ($transaction->items as $item) {
            PurchaseHistory::create([
                'user_id' => $transaction->user_id,
                'sellaccount_id' => $item['id'], // pastikan field ini dikirim di array items
                'transaction_id' => $transaction->id,
            ]);
        }
    
        return response()->json(['message' => 'Transaction approved and purchase history saved.']);
    }
    

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,complete,failed,refunded'
        ]);

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
