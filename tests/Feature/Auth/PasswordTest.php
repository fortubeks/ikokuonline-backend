<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PasswordTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_request_password_reset()
    {
        $user = User::factory()->create();

        $response = $this->postJson('/api/auth/password/forgot', [
            'email' => $user->email,
        ]);

        $response->assertStatus(200);
    }

    public function test_user_can_reset_password()
    {
        $user = User::factory()->create();
        $token = Password::createToken($user);

        $response = $this->postJson('/api/auth/password/reset', [
            'email' => $user->email,
            'token' => $token,
            'new_password' => 'newsecurepassword',
        ]);

        $response->assertStatus(200);
        $response->assertTrue(Hash::check('newsecurepassword', $user->fresh()->password));
    }

    public function test_user_can_change_password()
    {
        $user = User::factory()->create(['password' => bcrypt('oldpass')]);

        $response = $this->actingAs($user, 'api')->postJson('/api/auth/password/change', [
            'current_password' => 'oldpass',
            'new_password' => 'newpass123',
        ]);

        $response->assertStatus(200);
        $response->assertTrue(Hash::check('newpass123', $user->fresh()->password));
    }
}