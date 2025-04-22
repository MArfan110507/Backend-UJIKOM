<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    // ✅ Untuk USER: melihat histori pembayaran miliknya
    public function myPayments()
    {
        $payments = Payment::with('order.items.sellaccount')
            ->whereHas('order', function ($q) {
                $q->where('user_id', auth()->id());
            })
            ->orderByDesc('created_at')
            ->get();

        return response()->json($payments);
    }

    // ✅ Untuk ADMIN: melihat semua pembayaran (dashboard)
    public function index()
    {
        $this->authorize('admin'); // Gunakan Gate / Role untuk admin access
        
        $payments = Payment::with(['order.user', 'order.items.sellaccount'])
            ->orderByDesc('created_at')
            ->get();

        return response()->json($payments);
    }

    // ✅ Untuk ADMIN: menerima atau approve order manual
    public function approve($id)
    {
        $this->authorize('admin'); // Gunakan Gate / Role untuk admin access

        DB::beginTransaction();
        try {
            $payment = Payment::with('order')->findOrFail($id);
            
            if ($payment->status !== 'pending') {
                return response()->json(['message' => 'Pembayaran ini tidak dalam status pending'], 400);
            }

            $payment->status = 'paid';
            $payment->save();

            $payment->order->status = 'success';
            $payment->order->save();

            DB::commit();
            return response()->json(['message' => 'Pembayaran disetujui & order diterima']);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal menyetujui pembayaran', 'error' => $e->getMessage()], 500);
        }
    }
}