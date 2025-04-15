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

        $chats = Chat::where('sellaccount_id', $sellaccountId)
            ->where(function ($q) use ($userId, $receiverId) {
                $q->where('sender_id', $userId)->where('receiver_id', $receiverId)
                  ->orWhere('sender_id', $receiverId)->where('receiver_id', $userId);
            })
            ->orderBy('created_at')
            ->get();

        return response()->json($chats);
    }

    public function store(Request $request)
    {
        $request->validate([
            'sellaccount_id' => 'required|exists:sellaccounts,id',
            'receiver_id' => 'required|exists:users,id',
            'message' => 'required|string',
        ]);

        $chat = Chat::create([
            'sellaccount_id' => $request->sellaccount_id,
            'sender_id' => Auth::id(),
            'receiver_id' => $request->receiver_id,
            'message' => $request->message,
            'status' => 'pending',
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
