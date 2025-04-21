<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\ProfileResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\UserProfileResource;

class ProfileController extends Controller
{
    public function show($id)
    {
        $user = User::with('profile')->find($id);

        if ($user) {
            return new UserProfileResource($user);
        } else {
            return response()->json(['message' => 'User not found'], 404);
        }
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $photoPath = $request->file('photo')->store('profiles', 'public');

        $profile = Profile::updateOrCreate(
            ['user_id' => $user->id],
            ['photo' => $photoPath]
        );

        return new ProfileResource($profile);
    }

    public function update(Request $request, $id)
{
    Log::info('Update method called for user ID: ' . $id);

    $user = User::findOrFail($id);
    Log::info('User before update: ', $user->toArray());

    $validator = Validator::make($request->all(), [
        'name' => 'nullable|string|max:255',
        'email' => 'nullable|email|unique:users,email,' . $id,
        'password' => 'nullable|string|max:20',
        'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
    ]);

    if ($validator->fails()) {
        Log::error('Validation failed: ', $validator->errors()->toArray());
        return response()->json(['errors' => $validator->errors()], 422);
    }

    $validatedData = $validator->validated();
    Log::info('Validated data: ', $validatedData);

    if ($request->has('name')) {
        $user->name = $request->name;
    }

    if ($request->has('email')) {
        $user->email = $request->email;
    }

    if ($request->filled('password')) {
        $user->password = bcrypt($request->password);
    }

    $user->save();

    // ✅ update profile photo
    $profile = $user->profile;
    if ($profile) {
        if ($request->hasFile('photo')) {
            if ($profile->photo) {
                Storage::disk('public')->delete($profile->photo);
            }
            $profile->photo = $request->file('photo')->store('profiles', 'public');
            $profile->save();
        }
    } else {
        if ($request->hasFile('photo')) {
            $profile = new Profile();
            $profile->user_id = $user->id;
            $profile->photo = $request->file('photo')->store('profiles', 'public');
            $profile->save();
        }
    }

    // ✅ Refresh user and load profile again
    $user->refresh();
    $user->load('profile');

    return response()->json([
        'profile' => new UserProfileResource($user)
    ]);
}


    public function destroy()
    {
        $profile = Auth::user()->profile;
        if ($profile) {
            if ($profile->photo) {
                Storage::disk('public')->delete($profile->photo);
            }
            $profile->delete();
            return response()->json(['message' => 'Profile deleted successfully'], 200);
        } else {
            return response()->json(['message' => 'Profile not found'], 404);
        }
    }
}
