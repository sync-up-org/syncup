<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'id' => $user->id,
            'username' => $user->username,
            'email' => $user->email,
        ]);
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'username' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => ['required', Password::min(8)->mixedCase()->numbers()->symbols()],
            ]);
        } catch (ValidationException $e) {
            if ($e->validator->errors()->has('email')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Registration failed. Please check your input.',
                ], 422);
            }
            throw $e;
        }

        $validated['email'] = Str::of($validated['email'])->trim()->lower()->toString();

        $user = User::create([
            'username' => $validated['username'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Registered user successfully',
            'user_id' => $user->id,
        ], 201);
    }

    public function show(string $id)
    {
        //
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $rules = [
            'username' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('users')->ignore($user->id),
            ],
        ];

        $requestEmail = Str::of($request->input('email', ''))->trim()->lower()->toString();
        if ($requestEmail !== $user->email) {
            $rules['current_password'] = 'required|string';
        }

        $validated = $request->validate($rules);

        if ($request->filled('current_password')) {
            if (!Hash::check($validated['current_password'], $user->password)) {
                return response()->json([
                    'message' => 'Current password is incorrect.',
                ], 422);
            }
            unset($validated['current_password']);
        }

        $validated['email'] = $requestEmail;
        $user->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'User updated successfully',
            'data' => new UserResource($user),
        ], 200);
    }

    public function updatePassword(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'current_password' => 'required|string',
            'password' => ['required', Password::min(8)->mixedCase()->numbers()->symbols()],
        ]);

        if (!Hash::check($validated['current_password'], $user->password)) {
            return response()->json([
                'message' => 'Current password is incorrect.',
            ], 422);
        }

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return response()->json([
            'message' => 'Password updated successfully.',
        ]);
    }

    public function destroy(Request $request, string $id)
    {
        $user = User::find($id);

        if (!$user || (int) $user->id !== (int) $request->user()->id) {
            return response()->json([
                'message' => 'Forbidden',
            ], 403);
        }

        $user->delete();
        return response()->json([
            'message' => 'Successfully deleted user',
        ]);
    }
}
