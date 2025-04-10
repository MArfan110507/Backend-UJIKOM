<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Profile;

class ProfileController extends Controller
{
    // Menampilkan profile user (avatar, name, email)
    public function show()
    {
        $user = Auth::user();
        return response()->json([
            'name' => $user->name,
            'email' => $user->email,
            'avatar' => $user->profile ? $user->profile->avatar : null
        ]);
    }

    // Mengupdate avatar
    public function update(Request $request)
    {
        $request->validate([
            'avatar' => 'nullable|url', // Hanya URL avatar yang bisa diubah
        ]);

        $user = Auth::user();
        $profile = $user->profile ?: new Profile(['user_id' => $user->id]);

        $profile->avatar = $request->avatar;
        $profile->save();

        return response()->json([
            'message' => 'Profile updated successfully',
            'profile' => [
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => $profile->avatar
            ]
        ]);
    }

    // 🔹 Reset password
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:6|confirmed'
        ]);

        $user = Auth::user();

        // Cek apakah password lama benar
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json(['message' => 'Password lama salah'], 400);
        }

        // Update password baru
        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json(['message' => 'Password berhasil diubah']);
    }
}
