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

    public function create()
    {
        return view('article.create');
    }

    public function store(Request $request)
    
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'game' => 'nullable|string|max:255',
            'status' => 'required|in:draft,published',
            'image_url' => 'nullable|url',
        ]);

        $data = Article::create([
            'title' => $request->title,
            'content' => $request->content,
            'game' => $request->game,
            'status' => $request ? $request->status : 'draft',
            'image_url' => $request->image_url,
            'user_id' => Auth::id(),
        ]);

        return response()->json([
            'data' => $data
        ]);
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
            'status' => 'required|in:Published,Draft',
            'imageUrl' => 'nullable|url', // Match the frontend field name
        ]);

        $article = Article::create([
            'title' => $validated['title'],
            'content' => $validated['content'],
            'game' => $validated['game'],
            'status' => strtolower($validated['status']),
            'image_url' => $validated['imageUrl'], // Map to the correct DB column
            'user_id' => Auth::id(),
        ]);

        return response()->json([
            'id' => $article->id,
            'admin' => Auth::user()->name,
            'game' => $article->game,
            'avatarUrl' => Auth::user()->avatar_url ?? '/api/placeholder/48/48',
            'verified' => Auth::user()->verified ?? false,
            'timeAgo' => 'Baru saja • ',
            'imageUrl' => $article->image_url ?? '/api/placeholder/300/200',
            'title' => $article->title,
            'content' => $article->content,
            'details' => [],
            'status' => ucfirst($article->status),
            'date' => $article->created_at->format('Y-m-d'),
        ], 201);
    } catch (\Exception $e) {
        // Log the error and return helpful response
     
        return response()->json([
            'message' => 'Failed to create article',
            'error' => $e->getMessage()
        ], 500);
    }
}


    public function edit($id)
    {
        $article = Article::findOrFail($id);
        return view('article.edit', compact('article'));
    }

    public function update(Request $request, $id)
    {
        $article = Article::findOrFail($id);
        
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'game' => 'nullable|string|max:255',
            'status' => 'required|in:draft,published',
            'image_url' => 'nullable|url',
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