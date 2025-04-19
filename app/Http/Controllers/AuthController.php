<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users',
            'password' => 'required|string|min:6',
            'role'     => 'required|in:admin,user',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $role = $request->role;
        $adminId = $role === 'admin' ? $this->generateUniqueAdminId() : null;
        $userId  = $role === 'user'  ? $this->generateUniqueUserId()  : null;

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => $role,
            'admin_id' => $adminId,
            'user_id'  => $userId,
        ]);

        $user->profile()->create();

        $token = JWTAuth::fromUser($user);

        return response()->json([
            'message' => 'Register successful',
            'token'   => $token,
            'user'    => $user,
        ], 201);
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $user = JWTAuth::user();

        return response()->json([
            'message' => 'Login successful',
            'token'   => $token,
            'user'    => $user,
        ]);
    }

    public function profile()
    {
        return response()->json(JWTAuth::user());
    }

    public function logout()
    {
        JWTAuth::invalidate(JWTAuth::getToken());

        return response()->json(['message' => 'Logged out successfully']);
    }

    private function generateUniqueAdminId()
    {
        do {
            $id = rand(100000, 999999);
        } while (User::where('admin_id', $id)->exists());

        return $id;
    }

    private function generateUniqueUserId()
    {
        do {
            $id = rand(100000, 999999);
        } while (User::where('user_id', $id)->exists());

        return $id;
    }
}
