<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function show()
    {
        $user = Auth::user();
        $profile = Profile::where('user_id', $user->id)->first();

        return response()->json([
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'created_at' => $user->created_at->toDateTimeString(),
            'avatar' => $profile ? $profile->avatar : null,
        ]);
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        $profile = Profile::firstOrNew(['user_id' => $user->id]); // buat jika belum ada

        // Validasi input
        $request->validate([
            'nickname' => 'nullable|string|max:255',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'password' => 'nullable|min:8|confirmed',
        ]);

        // Update nickname
        if ($request->filled('nickname')) {
            $user->name = $request->nickname;
        }

        // Update avatar
        if ($request->hasFile('avatar')) {
            // Hapus avatar lama jika ada
            if ($profile->avatar) {
                $oldPath = str_replace('/storage/', 'public/', $profile->avatar);
                Storage::delete($oldPath);
            }

            // Upload dan simpan avatar baru
            $path = $request->file('avatar')->store('storage/profile_pictures');
            $profile->avatar = Storage::url($path); // hasilnya /storage/....
        }

        // Update password
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();
        $profile->save();

        return response()->json([
            'message' => 'Profile updated successfully.',
            'data' => [
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => $profile->avatar,
                'updated_at' => $user->updated_at
            ]
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'new_password' => 'required|min:8|confirmed',
        ]);

        $user = Auth::user();
        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json([
            'message' => 'Password has been reset successfully.'
        ]);
    }
}
