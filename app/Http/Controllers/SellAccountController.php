<?php

namespace App\Http\Controllers;

use App\Models\SellAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SellAccountController extends Controller
{
    public function index()
    {
        $accounts = SellAccount::all()->map(function ($account) {
            if (!auth()->check() || auth()->user()->role !== 'admin') {
                unset($account->game_email, $account->game_password);
            }
            return $account;
        });

        return response()->json($accounts);
    }

    public function store(Request $request)
    {
        $request->validate([
            'game' => 'required|string',
            'images' => 'nullable|array|max:5',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'stock' => 'required|integer',
            'game_server' => 'required|string', // Ganti INI sesuai kebutuhan
            'title' => 'required|string',
            'price' => 'required|numeric',
            'discount' => 'nullable|numeric',
            'level' => 'required|string',
            'features' => 'required|array',
            'game_email' => 'required|string',
            'game_password' => 'required|string',
        ]);

        $finalImages = [];
        $finalImagePaths = [];

        // Upload gambar lewat file
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $path = $file->store('public/sellaccount_images');
                $finalImages[] = asset(Storage::url($path));
                $finalImagePaths[] = $path; // Simpan path asli di storage
            }
        }

        // Jika ada gambar lewat URL
        if ($request->has('image_urls')) {
            foreach ($request->image_urls as $url) {
                if (filter_var($url, FILTER_VALIDATE_URL)) {
                    $finalImages[] = $url;
                    // Jika gambar dari URL, kita tidak punya path file, jadi kosongkan atau simpan null
                    $finalImagePaths[] = null;
                }
            }
        }

        if (count($finalImages) > 5) {
            return response()->json(['error' => 'Maksimal 5 gambar diperbolehkan.'], 422);
        }

        $sellAccount = SellAccount::create([
            'game'         => $request->game,
            'images'       => $finalImages,
            'image_paths'  => $finalImagePaths,
            'stock'        => $request->stock,
            'game_server'  => $request->game_server,
            'title'        => $request->title,
            'price'        => $request->price,
            'discount'     => $request->discount,
            'level'        => $request->level,
            'features'     => $request->features,
            'game_email'   => $request->game_email,
            'game_password'=> $request->game_password,
            'admin_id'     => auth()->id(), // Simpan admin id yang membuat sellaccount
        ]);

        return response()->json($sellAccount, 201);
    }

    public function show($id)
    {
        $account = SellAccount::findOrFail($id);

        if (!auth()->check() || auth()->user()->role !== 'admin') {
            unset($account->game_email, $account->game_password);
        }

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
            'game_server' => 'sometimes|required|string', // Ganti INI sesuai kebutuhan
            'title' => 'sometimes|required|string',
            'price' => 'sometimes|required|numeric',
            'discount' => 'nullable|numeric',
            'level' => 'sometimes|required|string',
            'features' => 'sometimes|required|array',
            'game_email' => 'sometimes|required|string',
            'game_password' => 'sometimes|required|string',
        ]);

        $finalImages = [];
        $finalImagePaths = [];

        // Update gambar lewat file
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $path = $file->store('public/sellaccount_images');
                $finalImages[] = asset(Storage::url($path));
                $finalImagePaths[] = $path;
            }
        }

        // Update jika ada gambar lewat URL
        if ($request->has('image_urls')) {
            foreach ($request->image_urls as $url) {
                if (filter_var($url, FILTER_VALIDATE_URL)) {
                    $finalImages[] = $url;
                    $finalImagePaths[] = null;
                }
            }
        }

        if (count($finalImages) > 5) {
            return response()->json(['error' => 'Maksimal 5 gambar diperbolehkan.'], 422);
        }

        $data = $request->only([
            'game',
            'stock',
            'game_server',
            'title',
            'price',
            'discount',
            'level',
            'game_email',
            'game_password'
        ]);

        if (!empty($finalImages)) {
            $data['images'] = $finalImages;
            $data['image_paths'] = $finalImagePaths;
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
