<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    // login
    public function login(Request $request) {
        $validated = $request->validate([
            'email' => 'required|exists:users,email',
            'password' => 'required|string|max:100'
        ]);

        $credentials = $request->only('email', 'password');

        if(Auth::attempt($credentials)) {
            $user = auth()->user();
            $token = $user->createToken('api-access-token')->plainTextToken;
            return response()->json([
                'status' => 'success',
                'userData' => $user,
                'token' => $token,
            ])->cookie('token', $token, 60 * 24, null, null, false, true);
        } else {
            return response()->json([
                'status' => 'failed',
                'message' => 'Invalid email or password'
            ]);
        }
    }

    // logout
    public function logout() {
        if(auth()->user()->currentAccessToken()->exists()) {
            auth()->user()->currentAccessToken()->delete();
        }
        return response()->json([
            'status' => 'success',
            'message' => 'Logout successful.'
        ]);
    }
}
