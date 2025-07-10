<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use App\Mail\EmailVerificationOtp;
use App\Mail\PasswordResetOtp;

use App\Models\User;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\VerifyEmailRequest;
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\ResetPasswordRequest;

use App\Events\UserRegistered;
use App\Http\Resources\UserResource;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    public function register(RegisterRequest $request){
        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'phone' => $request->phone,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        if($user){
            event(new UserRegistered($user));

            $token = $user->createToken('auth_token')->plainTextToken;//Auth::login($user);
            return response()->json([
                'status' => true,
                'message' => 'User created successfully',
                'user' => new UserResource($user),
                // 'authorisation' => [
                //     'token' => $token,
                //     'type' => 'bearer',
                // ]
            ], 201);
        }
    
    }

    public function login(LoginRequest $request)
    {
        //input validation is done by AuthRequest form request
        $credentials = $request->safe()->only('email', 'password');

        //attempt to log the user in and generate token
        if (!Auth::attempt($credentials)) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        $user = Auth::user();

        if (is_null($user->email_verified_at)) {
            return response()->json([
                'status' => false,
                'message' => 'Email is not verified.',
            ], 403);
        }

        $token = $user->createToken('auth_token')->plainTextToken;
        return response()->json([
                'status' => true,
                'user' => new UserResource($user),
                'authorisation' => [
                    'token' => $token,
                    'type' => 'bearer',
                ]
        ], 200);

    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json([
            'status' => true,
            'message' => 'Successfully logged out',
        ]);
    }

    public function verifyEmail(VerifyEmailRequest $request)
    {
        $user = User::where('email', $request->email)->first();

        if (!$user || !$user->email_verification_code) {
            return response()->json([
                'status'  => false,
                'message' => 'Verification code not found.',
            ], 404);
        }

        if ($user->email_verified_at) {
            return response()->json([
                'status'  => false,
                'message' => 'Email is already verified.',
            ], 400);
        }

        if ($user->email_verification_code !== $request->otp) {
            return response()->json([
                'status'  => false,
                'message' => 'Invalid OTP code.',
            ], 422);
        }

        if (now()->greaterThan($user->email_verification_expires_at)) {
            return response()->json([
                'status'  => false,
                'message' => 'Verification code has expired.',
            ], 422);
        }

        $user->email_verified_at = now();
        $user->email_verification_code = null;
        $user->email_verification_expires_at = null;

        if($user->save()){
            return response()->json([
                'status'  => true,
                'message' => 'Email verified successfully.',
            ]);
        }

        return response()->json([
            'status'  => false,
            'message' => 'Email verification failed.',
        ], 500);
    }

    public function resendVerification(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
        ]);

        $user = User::where('email', $request->email)->first();

        if ($user->email_verified_at) {
            return response()->json([
                'status' => false,
                'message' => 'Email already verified.',
            ], 400);
        }

        // Generate new OTP and expiration
        $user->generateEmailVerificationOtp(); 

        // Send email
        Mail::to($user->email)->send(new EmailVerificationOtp($user));

        return response()->json([
            'status' => true,
            'message' => 'Verification email resent.',
        ]);

    }


    public function refresh()
    {
        //refresh the authentication token
        return response()->json([
            'status' => true,
            'user' => Auth::user(),
            'authorisation' => [
                'token' => Auth::refresh(),
                'type' => 'bearer',
            ]
        ]);
    }

    public function changePassword(ChangePasswordRequest $request)
    {
        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'status' => false,
                'message' => 'Current password is incorrect',
            ], 403);
        }

        if (Hash::check($request->new_password, $user->password)) {
            return response()->json([
                'status' => false,
                'message' => 'New password must be different from current password',
            ], 422);
        }

        
        $user->update([
            'password' => Hash::make($request->new_password),
        ]);

        $request->user()->tokens()->delete();

        return response()->json([
            'status' => true,
            'message' => 'Password changed successfully',
        ]);
    }

    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
        ]);

        $user = User::where('email', $request->email)->first();

        $user->generatePasswordResetOtp();

        Mail::to($user->email)->send(new PasswordResetOtp($user));

        return response()->json([
            'status' => true,
            'message' => 'OTP sent to your email address',
        ]);
    }

    public function resetPassword(ResetPasswordRequest $request)
    {
        $user = User::where('email', $request->email)->first();

        if (
            !$user->password_reset_otp ||
            $user->password_reset_otp !== $request->otp ||
            now()->gt($user->password_reset_expires_at)
        ) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid or expired OTP',
            ], 422);
        }

        $user->update([
            'password' => Hash::make($request->password),
            'password_reset_otp' => null,
            'password_reset_expires_at' => null,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Password reset successful',
        ]);
    }

}
