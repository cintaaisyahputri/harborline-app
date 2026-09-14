<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\LoginVerificationCodeMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * POST /api/register
     * Issues a token immediately -- no email step for signup.
     */
    public function register(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            // Public self-registration can only claim operational roles -- 'admin'
            // is granted internally, never through this endpoint.
            'role'     => 'nullable|string|in:fleet_manager,warehouse,buyer',
        ]);

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
            'role'     => $data['role'] ?? 'buyer',
        ]);

        $token = $user->createToken('harborline-provisions')->plainTextToken;

        return response()->json([
            'user'  => $user,
            'token' => $token,
        ], 201);
    }

    /**
     * POST /api/login
     * Step 1: verify email + password, then email a 6-digit one-time code.
     * No token is issued here -- the client must call /api/login/verify next.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['These credentials do not match our manifest.'],
            ]);
        }

        $code = (string) random_int(100000, 999999);

        $user->forceFill([
            'login_code' => $code,
            'login_code_expires_at' => now()->addMinutes(10),
        ])->save();

        Mail::to($user->email)->send(new LoginVerificationCodeMail($user, $code));

        return response()->json([
            'message' => 'A verification code has been sent to your email.',
            'email' => $user->email,
            'expires_in_minutes' => 10,
        ]);
    }

    /**
     * POST /api/login/verify
     * Step 2: exchange email + the emailed code for a Sanctum token.
     */
    public function verifyLogin(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
            'code'  => 'required|string',
        ]);

        $user = User::where('email', $data['email'])->first();

        $invalid = ! $user
            || ! $user->login_code
            || ! hash_equals($user->login_code, $data['code'])
            || ! $user->login_code_expires_at
            || $user->login_code_expires_at->isPast();

        if ($invalid) {
            throw ValidationException::withMessages([
                'code' => ['That code is invalid or has expired. Please log in again to request a new one.'],
            ]);
        }

        $user->forceFill([
            'login_code' => null,
            'login_code_expires_at' => null,
            'email_verified_at' => $user->email_verified_at ?? now(),
        ])->save();

        $token = $user->createToken('harborline-provisions')->plainTextToken;

        return response()->json([
            'user'  => $user,
            'token' => $token,
        ]);
    }

    /**
     * POST /api/logout
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out.']);
    }

    /**
     * GET /api/me
     */
    public function me(Request $request)
    {
        return response()->json($request->user());
    }
}
