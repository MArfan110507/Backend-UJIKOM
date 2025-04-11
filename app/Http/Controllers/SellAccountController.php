<?php

namespace App\Http\Controllers;

use App\Models\SellAccount;
use Illuminate\Http\Request;

class SellAccountController extends Controller
{
    public function index()
    {
        return response()->json(SellAccount::all());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'game' => 'required|string',
            'images' => 'required',
            'stock' => 'required|integer|min:1',
            'server' => 'required|string',
            'title' => 'required|string',
            'price' => 'required|numeric',
            'discount' => 'nullable|numeric',
            'level' => 'required|string',
            'features' => 'required',
            'game_email' => 'required|string',
            'game_password' => 'required|string',
        ]);

        // Proses images
        $images = is_array($request->images) ? $request->images : [$request->images];
        if (count($images) > 5) {
            return response()->json(['error' => 'Maksimal 5 gambar diperbolehkan.'], 422);
        }
        $validated['images'] = json_encode($images);

        // Proses features
        $features = is_array($request->features) ? $request->features : [$request->features];
        $validated['features'] = json_encode($features);

        $sellAccount = SellAccount::create($validated);

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

        $validated = $request->validate([
            'game' => 'sometimes|required|string',
            'images' => 'sometimes|required',
            'stock' => 'sometimes|required|integer|min:1',
            'server' => 'sometimes|required|string',
            'title' => 'sometimes|required|string',
            'price' => 'sometimes|required|numeric',
            'discount' => 'nullable|numeric',
            'level' => 'sometimes|required|string',
            'features' => 'sometimes|required',
            'game_email' => 'sometimes|required|string',
            'game_password' => 'sometimes|required|string',
        ]);

        // Proses images jika dikirim
        if ($request->has('images')) {
            $images = is_array($request->images) ? $request->images : [$request->images];

            if (count($images) > 5) {
                return response()->json(['error' => 'Maksimal 5 gambar diperbolehkan.'], 422);
            }

            $validated['images'] = json_encode($images);
        }

        // Proses features jika dikirim
        if ($request->has('features')) {
            $features = is_array($request->features) ? $request->features : [$request->features];
            $validated['features'] = json_encode($features);
        }

        $sellAccount->update($validated);

        return response()->json($sellAccount);
    }

    public function destroy($id)
    {
        $account = SellAccount::findOrFail($id);
        $account->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
