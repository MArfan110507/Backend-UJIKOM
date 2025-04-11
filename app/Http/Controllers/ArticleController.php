<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;


class ArticleController extends Controller
{

    public function index()
    {
        $articles = Article::with('user')->latest()->get();
        return view('article.index', compact('articles'));
    }

    // API endpoint for React
    public function apiIndex()
    {
        $articles = Article::with('user:id,name')->latest()->get()->map(function ($article) {
            return [
                'id' => $article->id,
                'admin' => $article->user->name,
                'game' => $article->game,
                'avatarUrl' => $article->user->avatar_url ?? '/api/placeholder/48/48',
                'verified' => $article->user->verified ?? false,
                'timeAgo' => $article->created_at->diffForHumans() . ' • ',
                'imageUrl' => $article->image_url ?? '/api/placeholder/300/200',
                'title' => $article->title,
                'content' => $article->content,
                'details' => [],
                'status' => ucfirst($article->status),
                'date' => $article->created_at->format('Y-m-d'),
            ];
        });

        return response()->json($articles);
    }

    // API endpoint for storing articles from React
    public function apiStore(Request $request)
{
    if (!Auth::check()) {
        return response()->json(['message' => 'Unauthorized'], 401);
    }

    try {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'game' => 'required|string|max:255',
            'status' => 'required|in:published,draft',
            'image' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048', // ✅ gunakan 'image' bukan 'image_url'
        ]);

        // ✅ Simpan gambar jika ada
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('articles', 'public'); // simpan di storage/app/public/articles
        }

        $article = Article::create([
            'title' => $validated['title'],
            'content' => $validated['content'],
            'game' => $validated['game'],
            'status' => strtolower($validated['status']),
            'image_url' => $imagePath ? '/storage/' . $imagePath : null, // ✅ path benar
            'user_id' => Auth::id(),
        ]);

        return response()->json([
            'id' => $article->id,
            'admin' => Auth::user()->name,
            'game' => $article->game,
            'avatarUrl' => Auth::user()->avatar_url ?? '/api/placeholder/48/48',
            'verified' => Auth::user()->verified ?? false,
            'timeAgo' => 'Baru saja • ',
            'imageUrl' => $article->image_url
                ? asset($article->image_url)
                : '/api/placeholder/300/200',
            'title' => $article->title,
            'content' => $article->content,
            'details' => [],
            'status' => ucfirst($article->status),
            'date' => $article->created_at->format('Y-m-d'),
        ], 201);
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Failed to create article',
            'error' => $e->getMessage()
        ], 500);
    }
}

    public function update(Request $request, $id)
    {
        $article = Article::findOrFail($id);

        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'game' => 'nullable|string|max:255',
            'status' => 'required|in:draft,published',
            'image' => 'nullable|url',
        ]);

        $article->update([
            'title' => $request->title,
            'content' => $request->content,
            'game' => $request->game,
            'status' => $request->status,
            'image_url' => $request->image_url,
        ]);

        return redirect()->route('article.index')->with('success', 'Article berhasil diperbarui.');
    }

    // API endpoint for toggling article status
    public function apiToggleStatus($id)
    {
        $article = Article::findOrFail($id);
        $newStatus = $article->status === 'published' ? 'draft' : 'published';
        $article->status = $newStatus;
        $article->save();

        return response()->json([
            'status' => ucfirst($newStatus)
        ]);
    }

    public function destroy($id)
    {
        $article = Article::findOrFail($id);
        $article->delete();

        return redirect()->route('article.index')->with('success', 'Article berhasil dihapus.');
    }

    // API endpoint for deleting articles
    public function apiDestroy($id)
    {
        $article = Article::findOrFail($id);
        $article->delete();

        return response()->json([
            'message' => 'Article berhasil dihapus'
        ]);
    }
}