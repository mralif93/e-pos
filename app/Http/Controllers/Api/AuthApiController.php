<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use App\Domains\Security\Actions\LogAuditAction;

class AuthApiController extends Controller
{
    /**
     * Authenticate a user and return a Sanctum token.
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'device_name' => 'nullable|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Revoke previous tokens if needed (optional)
        // $user->tokens()->delete();

        $token = $user->createToken($request->device_name ?? 'pos_terminal')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Logged in successfully',
            'data' => [
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'outlet_id' => $user->outlet_id,
                    'outlet' => $user->outlet ? [
                        'id' => $user->outlet->id,
                        'name' => $user->outlet->name,
                        'code' => $user->outlet->outlet_code,
                    ] : null,
                ],
            ]
        ]);
    }

    /**
     * Get the authenticated user's profile and outlet context.
     */
    public function me(Request $request)
    {
        $user = $request->user();
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'outlet_id' => $user->outlet_id,
                'outlet' => $user->outlet,
            ]
        ]);
    }

    /**
     * Log out and revoke the current token.
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully',
        ]);
    }

    /**
     * Send a password reset link (via email).
     */
    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        // In a real API-first app, you'd send an email with a token/link
        // For POS terminals, often a manager reset is preferred,
        // but this provides the standard flow.
        $status = Password::sendResetLink($request->only('email'));

        return $status === Password::RESET_LINK_SENT
            ? response()->json(['success' => true, 'message' => __($status)])
            : response()->json(['success' => false, 'message' => __($status)], 422);
    }
    
    /**
     * Validate a user's PIN for sensitive operations (e.g. void, unlock).
     */
    public function verifyPin(Request $request)
    {
        $request->validate(['pin' => 'required|string|size:4']);
        $user = auth()->user();

        // Check if current user PIN matches or if a manager/admin PIN is used
        if ($user && (string) $user->pin === (string) $request->pin) {
            return response()->json(['success' => true, 'message' => 'PIN verified', 'user_id' => $user->id]);
        }

        $manager = User::where('pin', $request->pin)
            ->whereIn('role', ['Manager', 'Admin', 'Super Admin'])
            ->first();

        if ($manager) {
            return response()->json([
                'success' => true, 
                'message' => 'Manager PIN verified', 
                'user_id' => $manager->id,
                'manager_name' => $manager->name
            ]);
        }

        return response()->json(['success' => false, 'message' => 'Invalid PIN'], 403);
    }
}
