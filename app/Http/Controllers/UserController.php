<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function register(RegisterUserRequest $request)
    {

        $validatedData = $request->validated();

        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('avatars', 'public');
            $validatedData['avatar'] = $path;
        }

        $user = User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
            'avatar' => $validatedData['avatar'] ?? null,
            'bio' => $validatedData['bio'] ?? null,
            'role' => 'user',
        ]);

        return response()->json([
            'message' => 'User created successfully',
            'user' => $user
        ], 201);
    }

    // tommorow task is to create function to update user informations and to allow to user to change the password


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



    public function searchUser(Request $request)
    {
        try {

            $search = $request->query('q');
            if (!$search) return response()->json(['Please provide a search query using ?q=kewword'], 404);


            $query = User::where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%");
            });

            $users = $query->orderBy('created_at', 'desc')->get();

            return response()->json([
                'search_query' => $search,
                'results_count' => $users->count(),
                'users' => UserResource::collection($users),
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'category not found exception', 'message' => $e->getMessage()], 404);
        } catch (Exception $e) {
            return response()->json(['message' => 'error happened while searching'], 400);
        }
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
