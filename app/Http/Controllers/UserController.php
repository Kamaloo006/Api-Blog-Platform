<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:200',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|confirmed|min:8'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return response()->json(['message' => 'user created successfuly', 'user' => $user], 200);
    }
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:8'
        ]);

        if (!Auth::attempt($request->only('email', 'password')))
            return response()->json(["message" => "email or password Not Valid"], 401);

        $user = User::where('email', $request->email)->firstOrFail();
        $token = $user->createToken('Auth_Token')->plainTextToken;

        return response()->json([
            'message' => 'User logged in successfully',
            'user' => $user,
            'token' => $token
        ], 200);
    }


    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'user logout successfuly'], 200);
    }


    public function appointUser($user_id)
    {
        $user = User::findOrFail($user_id);

        if ($user->role !== 'admin') {
            $user->role = 'admin';
            $user->save();
            return response()->json(['user' => $user->name, 'message' => 'is Now Admin'], 200);
        }
        return response()->json(['message' => 'User is Already an Admin'], 400);
    }
}
