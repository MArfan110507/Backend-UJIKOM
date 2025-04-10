<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FAQChat;
use App\Models\FAQ;
use Illuminate\Support\Facades\Auth;

class FAQChatController extends Controller
{
    // Mengambil semua pesan dalam 1 laporan
    public function index($faq_id)
    {
        $faq = FAQ::findOrFail($faq_id);

        return response()->json($faq->chats()->with('user')->get());
    }

    // Menambahkan pesan ke dalam laporan
    public function store(Request $request, $faq_id)
    {
        $request->validate(['message' => 'required|string']);

        $faq = FAQ::findOrFail($faq_id);

        $chat = FAQChat::create([
            'faq_id' => $faq->id,
            'user_id' => Auth::id(),
            'message' => $request->message,
        ]);

        return response()->json(['message' => 'Pesan terkirim', 'chat' => $chat]);
    }
}
