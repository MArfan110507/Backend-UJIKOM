<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FAQ;
use Illuminate\Support\Facades\Auth;

class FAQController extends Controller
{
    // Menampilkan semua laporan user (khusus admin)
    public function index()
    {
        $this->authorize('admin'); // Pastikan hanya admin yang bisa melihat semua laporan

        return response()->json(FAQ::with('user')->get());
    }

    // User membuat laporan baru
    public function store(Request $request)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        $faq = FAQ::create([
            'user_id' => Auth::id(),
            'subject' => $request->subject,
            'message' => $request->message,
        ]);

        return response()->json(['message' => 'Laporan berhasil dikirim', 'faq' => $faq], 201);
    }

    // Admin menutup laporan setelah selesai
    public function close($id)
    {
        $faq = FAQ::findOrFail($id);
        $this->authorize('admin');

        $faq->update(['status' => 'resolved']);

        return response()->json(['message' => 'Laporan telah ditutup']);
    }
}
