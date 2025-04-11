<?php

namespace App\Http\Controllers;

use App\Models\SellAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SellAccountController extends Controller
{
    public function index()
    {
        return response()->json(SellAccount::all());
    }

    public function store(Request $request)
    {
        $request->validate([
            'game' => 'required|string',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'image_urls' => 'nullable|array',
            'image_urls.*' => 'nullable|url',
            'stock' => 'required|integer|min:1',
            'server' => 'required|string',
            'title' => 'required|string',
            'price' => 'required|numeric',
            'discount' => 'nullable|numeric',
            'level' => 'required|string',
            'features' => 'required|array',
            'game_email' => 'required|string',
            'game_password' => 'required|string',
        ]);

        $finalImages = [];

        // Handle upload file
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $path = $file->store('public/sellaccount_images');
                $finalImages[] = asset(Storage::url($path)); // full URL
            }
        }

        // Handle URL gambar dari frontend
        if ($request->has('image_urls')) {
            foreach ($request->image_urls as $url) {
                if (filter_var($url, FILTER_VALIDATE_URL)) {
                    $finalImages[] = $url;
                }
            }
        }

        if (count($finalImages) > 5) {
            return response()->json(['error' => 'Maksimal 5 gambar diperbolehkan.'], 422);
        }

        $sellAccount = SellAccount::create([
            'game' => $request->game,
            'images' => $finalImages,
            'stock' => $request->stock,
            'server' => $request->server,
            'title' => $request->title,
            'price' => $request->price,
            'discount' => $request->discount,
            'level' => $request->level,
            'features' => $request->features,
            'game_email' => $request->game_email,
            'game_password' => $request->game_password,
        ]);

        return response()->json($sellAccount, 201);
    }

    public function show($id)
    {
        $account = SellAccount::findOrFail($id);
        return response()->json($account);
    }

    public function update(Request $request, $id)
    {
        $sellAccount = SellAccount::findOrFail($id);

        $request->validate([
            'game' => 'sometimes|required|string',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'image_urls' => 'nullable|array',
            'image_urls.*' => 'nullable|url',
            'stock' => 'sometimes|required|integer|min:1',
            'server' => 'sometimes|required|string',
            'title' => 'sometimes|required|string',
            'price' => 'sometimes|required|numeric',
            'discount' => 'nullable|numeric',
            'level' => 'sometimes|required|string',
            'features' => 'sometimes|required|array',
            'game_email' => 'sometimes|required|string',
            'game_password' => 'sometimes|required|string',
        ]);

        $finalImages = [];

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $path = $file->store('public/sellaccount_images');
                $finalImages[] = asset(Storage::url($path)); // full URL
            }
        }

        if ($request->has('image_urls')) {
            foreach ($request->image_urls as $url) {
                if (filter_var($url, FILTER_VALIDATE_URL)) {
                    $finalImages[] = $url;
                }
            }
        }

        if (count($finalImages) > 5) {
            return response()->json(['error' => 'Maksimal 5 gambar diperbolehkan.'], 422);
        }

        $data = $request->only([
            'game', 'stock', 'server', 'title', 'price',
            'discount', 'level', 'game_email', 'game_password'
        ]);

        if (!empty($finalImages)) {
            $data['images'] = $finalImages;
        }

        if ($request->has('features')) {
            $data['features'] = $request->features;
        }

        $sellAccount->update($data);

        return response()->json($sellAccount);
    }

    public function destroy($id)
    {
        $account = SellAccount::findOrFail($id);
        $account->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
