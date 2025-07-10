<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Resources\UserResource;

class SocialAuthController extends Controller
{
    public function redirect($provider)
    {
        return Socialite::driver($provider)->stateless()->redirect();
    }

    public function callback($provider)
    {
        try {
            $socialUser = Socialite::driver($provider)->stateless()->user();
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'messsage' => 'Unable to authenticate.'
            ], 400);
        }

        $user = User::where('email', $socialUser->getEmail())->first();

        if (!$user) {
            $full_name = trim($socialUser->getName());
            $name_array = explode(' ', $full_name, 2);

            $first_name = $nameParts[0] ?? 'Google';
            $last_name  = $nameParts[1] ?? 'User';
            $user = User::create([
                'first_name' => $first_name,
                'last_name' => $last_name,
                'email' => $socialUser->getEmail(),
                'email_verified_at' => now(),
                'password' => Hash::make(Str::random(16)),
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => true,
            'message' => 'Login successful',
            'user' => new UserResource($user),,
            'authorisation' => [
                'token' => $token,
                'type' => 'bearer',
            ]
        ]);
    }
}
