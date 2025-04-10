<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\JualAkun;

class JualAkunController extends Controller
{
    /**
     * Get all jual akun records.
     */
    public function index()
    {
        return response()->json(JualAkun::all());
    }

    /**
     * Store a new jual akun record.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'game' => 'required|string',
            'image' => 'required|string',
            'images' => 'required|array',
            'stock' => 'required|integer',
            'server' => 'required|string',
            'title' => 'required|string',
            'price' => 'required|numeric',
            'discount' => 'nullable|numeric',
            'rating' => 'nullable|numeric|min:0|max:5',
            'level' => 'required|string',
            'features' => 'required|array',
        ]);

        $jualAkun = JualAkun::create($validated);

        return response()->json(['message' => 'Akun game berhasil ditambahkan', 'data' => $jualAkun], 201);
    }

    /**
     * Show a specific jual akun record.
     */
    public function show($id)
    {
        $jualAkun = JualAkun::find($id);
        if (!$jualAkun) {
            return response()->json(['message' => 'Akun tidak ditemukan'], 404);
        }
        return response()->json($jualAkun);
    }

    /**
     * Update a jual akun record.
     */
    public function update(Request $request, $id)
    {
        $jualAkun = JualAkun::find($id);
        if (!$jualAkun) {
            return response()->json(['message' => 'Akun tidak ditemukan'], 404);
        }

        $validated = $request->validate([
            'game' => 'sometimes|string',
            'image' => 'sometimes|string',
            'images' => 'sometimes|array',
            'stock' => 'sometimes|integer',
            'server' => 'sometimes|string',
            'title' => 'sometimes|string',
            'price' => 'sometimes|numeric',
            'discount' => 'nullable|numeric',
            'level' => 'sometimes|string',
            'features' => 'sometimes|array',
        ]);

        $jualAkun->update($validated);

        return response()->json(['message' => 'Akun game berhasil diperbarui', 'data' => $jualAkun]);
    }

    /**
     * Delete a jual akun record.
     */
    public function destroy($id)
    {
        $jualAkun = JualAkun::find($id);
        if (!$jualAkun) {
            return response()->json(['message' => 'Akun tidak ditemukan'], 404);
        }

        $jualAkun->delete();

        return response()->json(['message' => 'Akun game berhasil dihapus']);
    }
}
