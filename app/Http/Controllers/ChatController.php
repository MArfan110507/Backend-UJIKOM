<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    public function index($sellaccountId, $receiverId)
    {
        $userId = Auth::id();

        $chats = Chat::with(['sender.profile', 'receiver.profile', 'sellAccount'])
            ->where('sellaccount_id', $sellaccountId)
            ->where(function ($q) use ($userId, $receiverId) {
                $q->where('sender_id', $userId)->where('receiver_id', $receiverId)
                    ->orWhere('sender_id', $receiverId)->where('receiver_id', $userId);
            })
            ->orderBy('created_at')
            ->get()
            ->map(function ($chat) {
                return [
                    'id' => $chat->id,
                    'message' => $chat->message,
                    'type' => $chat->type,
                    'status' => $chat->status,
                    'created_at' => $chat->created_at,
                    'sender' => [
                        'id' => $chat->sender_id,
                        'nickname' => $chat->sender->profile->nickname ?? $chat->sender->name,
                        'photo' => $chat->sender->profile->photo ? asset('storage/' . $chat->sender->profile->photo) : null,
                    ],
                    'receiver' => [
                        'id' => $chat->receiver_id,
                        'nickname' => $chat->receiver->profile->nickname ?? $chat->receiver->name,
                        'photo' => $chat->receiver->profile->photo ? asset('storage/' . $chat->receiver->profile->photo) : null,
                    ],
                    'sellaccount' => $chat->sellAccount ? [
                        'title' => $chat->sellAccount->title,
                        'image' => $chat->sellAccount->images[0] ?? null,
                        'price' => $chat->sellAccount->price,
                    ] : null
                ];
            });

        return response()->json($chats);
    }


    public function store(Request $request)
    {
        $request->validate([
            'sellaccount_id' => 'nullable|exists:sellaccounts,id',
            'receiver_id' => 'required|exists:users,id',
            'message' => 'required_if:type,text|string|nullable',
            'type' => 'in:text,info',
        ]);

        $senderId = Auth::id();
        $type = $request->type ?? 'text';

        $message = $request->message;

        // Jika type info, ambil deskripsi dari SellAccount
        if ($type === 'info' && $request->sellaccount_id) {
            $account = \App\Models\SellAccount::findOrFail($request->sellaccount_id);

            $message = "🛒 *{$account->title}*\n💰 Rp " . number_format($account->price) . "\n📦 Stock: {$account->stock}\n🎮 Level: {$account->level}";
        }

        $chat = Chat::create([
            'sellaccount_id' => $request->sellaccount_id,
            'sender_id' => $senderId,
            'receiver_id' => $request->receiver_id,
            'message' => $message,
            'status' => 'pending',
            'type' => $type,
        ]);

        return response()->json($chat, 201);
    }


    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,accept',
        ]);

        $chat = Chat::findOrFail($id);
        $chat->status = $request->status;
        $chat->save();

        return response()->json(['message' => 'Status updated.']);
    }

}
