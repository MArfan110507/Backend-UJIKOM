<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ProfileController extends Controller
{
    /**
     * Menampilkan profil user
     */
    public function show($id = null)
    {
        // Jika ID tidak disediakan, gunakan user yang sedang login
        $user = $id ? User::findOrFail($id) : Auth::user();
        
        // Pastikan profil tersedia
        $profile = $user->profile;
        if (!$profile) {
            $profile = $user->profile()->create();
        }
        
        $photoUrl = null;
        if ($profile->photo) {
            $photoUrl = url('storage/' . $profile->photo);
        }
        
        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'profile' => [
                    'photo_url' => $photoUrl,
                    'bio' => $profile->bio,
                    'address' => $profile->address,
                ],
            ],
        ]);
    }
    
    /**
     * Update profil user (termasuk data user dan profile)
     */
    public function update(Request $request, $id = null)
    {
        // Jika ID tidak disediakan, gunakan user yang sedang login
        $authUser = Auth::user();
        $id = $id ?: $authUser->id;
        
        // Cek akses
        if ($authUser->id != $id && $authUser->role != 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        // Cari user yang akan diupdate
        $user = User::findOrFail($id);
        
        Log::info('Update profile request', ['data' => $request->all()]);
        
        // Validasi input
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $id,
            'password' => 'sometimes|string|min:6',
            'photo' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048',
            'bio' => 'sometimes|string|nullable',
            'address' => 'sometimes|string|nullable',
        ]);
        
        // Update user data
        if ($request->has('name')) {
            $user->name = $request->name;
        }
        
        if ($request->has('email')) {
            $user->email = $request->email;
        }
        
        if ($request->has('password')) {
            $user->password = Hash::make($request->password);
        }
        
        $user->save();
        
        // Pastikan profile exists
        $profile = $user->profile;
        if (!$profile) {
            $profile = $user->profile()->create();
        }
        
        // Update profile data
        $profileData = [];
        
        if ($request->has('bio')) {
            $profileData['bio'] = $request->bio;
        }
        
        if ($request->has('address')) {
            $profileData['address'] = $request->address;
        }
        
        if (!empty($profileData)) {
            $profile->update($profileData);
        }
        
        // Handle photo upload
        if ($request->hasFile('photo')) {
            try {
                // Hapus foto lama jika ada
                if ($profile->photo) {
                    Storage::disk('public')->delete($profile->photo);
                }
                
                // Upload dan simpan path foto baru
                $photoPath = $request->file('photo')->store('profiles', 'public');
                $profile->update(['photo' => $photoPath]);
                
                Log::info('Photo uploaded successfully', ['path' => $photoPath]);
            } catch (\Exception $e) {
                Log::error('Error uploading photo: ' . $e->getMessage());
                return response()->json(['message' => 'Error uploading photo: ' . $e->getMessage()], 500);
            }
        }
        
        // Reload user dengan profile
        $user->refresh();
        $profile = $user->profile;
        
        $photoUrl = $profile->photo ? url('storage/' . $profile->photo) : null;
        
        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'profile' => [
                    'photo_url' => $photoUrl,
                    'bio' => $profile->bio,
                    'address' => $profile->address,
                ],
            ],
        ]);
    }
    
    /**
     * Khusus untuk upload foto profil
     */
    public function uploadPhoto(Request $request)
    {
        $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
        
        $user = Auth::user();
        
        try {
            // Pastikan profile exists
            $profile = $user->profile;
            if (!$profile) {
                $profile = $user->profile()->create();
            }
            
            // Hapus foto lama jika ada
            if ($profile->photo) {
                Storage::disk('public')->delete($profile->photo);
            }
            
            // Upload dan simpan path foto baru
            $photoPath = $request->file('photo')->store('profiles', 'public');
            $profile->update(['photo' => $photoPath]);
            
            return response()->json([
                'message' => 'Photo uploaded successfully',
                'photo_url' => url('storage/' . $photoPath),
            ]);
        } catch (\Exception $e) {
            Log::error('Error uploading photo: ' . $e->getMessage());
            return response()->json(['message' => 'Error uploading photo: ' . $e->getMessage()], 500);
        }
    }
    
    /**
     * Hapus foto profil
     */
    public function deletePhoto()
    {
        $user = Auth::user();
        $profile = $user->profile;
        
        if (!$profile || !$profile->photo) {
            return response()->json(['message' => 'No photo to delete'], 404);
        }
        
        try {
            Storage::disk('public')->delete($profile->photo);
            $profile->update(['photo' => null]);
            
            return response()->json(['message' => 'Photo deleted successfully']);
        } catch (\Exception $e) {
            Log::error('Error deleting photo: ' . $e->getMessage());
            return response()->json(['message' => 'Error deleting photo: ' . $e->getMessage()], 500);
        }
    }
}