<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

     /**
     * Test that a user can login with correct data
     */
    public function test_user_can_login_with_correct_data()
    {
        $user = User::factory()->create(['password' => bcrypt('password123')]);

        $response = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure(['token', 'user']);
    }


    /**
     * Test that a user cannot login with incomplete data
     */
    public function test_user_cannot_login_with_incomplete_data()
    {
        $response = $this->postJson('/api/auth/login', [
            
        ]);

        // Assert that the response status is 422
        $response->assertStatus(422);

        $response->assertJsonStructure([
            'status',
            'message',
            'errors' => [
                
            ],
        ]);

        $response->assertFalse($response->json('status'));
        $response->assertEquals('Request Failed', $response->json('message'));
        $response->assertJsonValidationErrors(['email', 'password']);
    }

    /**
     * Test that a user cannot login with invalid credentials
     */
    public function test_user_cannot_login_with_invalid_credentials()
    {
        $user = User::factory()->create(['password' => bcrypt('password123')]);

        $response = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401);
    }

     /**
     * Test that an unverified user cannot login
     */
    public function test_unverified_user_cannot_login()
    {
        $user = User::factory()->create([
            'email' => 'unverified@example.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => null,
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'unverified@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(401);

        $response->assertJson([
            'status' => false,
            'message' => 'Email not verified.',
        ]);
    }

    /**
     * Test that a user can logout
     */
    public function test_user_can_logout()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
                        ->postJson('/api/auth/logout');

        $response->assertStatus(200);

        $response->assertJson([
            'status' => true,
            'message' => 'Logged out successfully',
        ]);
    }


}