<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PurchaseHistory;
use Illuminate\Support\Facades\Auth;

class PurchaseHistoryController extends Controller
{
    public function index()
    {
        return response()->json(PurchaseHistory::where('user_id', Auth::id())->get());
    }

    public function store(Request $request)
    {
        $request->validate([
            'jualakun_id' => 'required|exists:jualakuns,id',
            'total_price' => 'required|numeric',
            'purchase_date' => 'required|date',
            'game_email' => 'required|string',
            'game_password' => 'required|string'
        ]);

        $purchase = PurchaseHistory::create([
            'user_id' => Auth::id(),
            'jualakun_id' => $request->jualakun_id,
            'total_price' => $request->total_price,
            'purchase_date' => $request->purchase_date,
            'game_email' => $request->game_email,
            'game_password' => $request->game_password,
        ]);

        return response()->json($purchase, 201);
    }

    public function show($id)
    {
        $purchase = PurchaseHistory::where('user_id', Auth::id())->findOrFail($id);
        return response()->json($purchase);
    }
}