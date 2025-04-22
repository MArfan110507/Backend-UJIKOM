<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Orders;
use App\Models\Payment;
use Midtrans\Notification;
use Midtrans\Config;
use Illuminate\Support\Facades\Log;

class MidtransController extends Controller
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
    
    public function callback(Request $request)
    {
        // Log seluruh request untuk debugging
        Log::info('Midtrans callback', $request->all());
        
        try {
            $notif = new Notification();
            
            $transaction = $notif->transaction_status;
            $type = $notif->payment_type;
            $order_id = str_replace('ORDER-', '', $notif->order_id);
            $fraud = $notif->fraud_status;
            $transaction_id = $notif->transaction_id;

            Log::info("Processing order: $order_id, status: $transaction");

            $order = Orders::find($order_id);
            if (!$order) {
                Log::error("Order tidak ditemukan: $order_id");
                return response()->json(['message' => 'Order tidak ditemukan'], 404);
            }

            $payment = $order->payment;
            if (!$payment) {
                Log::error("Pembayaran tidak ditemukan untuk order: $order_id");
                return response()->json(['message' => 'Pembayaran tidak ditemukan'], 404);
            }

            // Update payment dengan data transaksi
            $payment->transaction_id = $transaction_id;
            $payment->payment_type = $type;
            $payment->transaction_time = date('Y-m-d H:i:s');
            $payment->gross_amount = $notif->gross_amount;

            // Update status berdasarkan status dari Midtrans
            if ($transaction == 'capture') {
                if ($type == 'credit_card') {
                    if ($fraud == 'challenge') {
                        $order->status = 'pending';
                        $payment->status = 'challenge';
                    } else {
                        $order->status = 'success';
                        $payment->status = 'paid';
                    }
                }
            } else if ($transaction == 'settlement') {
                $order->status = 'success';
                $payment->status = 'paid';
            } else if ($transaction == 'pending') {
                $order->status = 'pending';
                $payment->status = 'pending';
            } else if (in_array($transaction, ['deny', 'expire', 'cancel'])) {
                $order->status = 'failed';
                $payment->status = $transaction;
            }

            $order->save();
            $payment->save();

            Log::info("Updated order $order_id to status: {$order->status}");
            
            return response()->json(['message' => 'Callback processed successfully']);
            
        } catch (\Exception $e) {
            Log::error('Error processing Midtrans callback: ' . $e->getMessage());
            return response()->json(['message' => 'Error processing callback', 'error' => $e->getMessage()], 500);
        }
    }
}