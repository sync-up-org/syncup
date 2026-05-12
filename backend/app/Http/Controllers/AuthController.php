<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request) 
    { 
        $request->validate([
            'email' => 'required|string|max:255',
            'password' => 'required|string'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => 'These credentials do not match our records.',
            ]);
        }

        Auth::login($user);

        return response()->json([
            'success' => true,
            'message' => 'Logged in successfully',
            'token' => $user->createToken('api-token')->plainTextToken,
            'token_type' => 'Bearer',
        ], 200);
    }
}
