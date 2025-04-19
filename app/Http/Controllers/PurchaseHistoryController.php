<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PurchaseHistory;
use Illuminate\Support\Facades\Auth;

class PurchaseHistoryController extends Controller
{
    public function history()
    {
        $user = Auth::user();

        $history = PurchaseHistory::with('sellAccount')
            ->where('user_id', $user->id)
            ->get()
            ->map(function ($item) {
                $account = $item->sellAccount;

                return [
                    'title' => $account->title,
                    'game' => $account->game,
                    'game_server' => $account->game_server,
                    'level' => $account->level,
                    'price' => $account->price,
                    'features' => $account->features,
                    'images' => $account->images,
                    'game_email' => $account->game_email,
                    'game_password' => $account->game_password,
                    'transaction_id' => $item->transaction_id,
                    'purchased_at' => $item->created_at->toDateTimeString(),
                ];
            });

        return response()->json($history);
    }
}
