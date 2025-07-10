<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

use Illuminate\Support\Facades\Mail;
use App\Mail\EmailVerificationOtp;

use Illuminate\Support\Facades\Event;
use App\Events\UserRegistered;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that a user can register with correct data
     */
    public function test_user_can_register()
    {
        $response = $this->postJson('/api/auth/register', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'phone' => '+2349055667788',
            'email' => 'john@example.com',
            'password' => 'password123',
        ]);
        
        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
        ]);

        $response->assertStatus(201)
                 ->assertJsonStructure([
                    'status',
                    'message',
                    'user'
                ]);

        $response->assertJsonFragment([
            'email' => 'john@example.com',
            'first_name' => 'John',
        ]);
    }

    /**
     * Test that a user cannot register with incompleted data
     */
    public function test_user_cannot_register_with_incomplete_data(): void
    {
        // Attempt to register a new user without name
        $response = $this->postJson('/api/auth/register', [
            
        ]);
        
        // Assert that the response status is 422
        $response->assertStatus(422);

        // Assert that the returned response structure matches the expected structure
        $response->assertJsonStructure([
            'status',
            'message',
            'errors' => [
                
            ],
        ]);

        $this->assertFalse($response->json('status'));
        $this->assertEquals('Request Failed', $response->json('message'));
        $response->assertJsonValidationErrors(['first_name', 'last_name', 'email', 'password']);
    }

    /**
     * Test that a user cannot use password shorter than 8 characters
     */
    public function test_user_cannot_register_with_weak_password()
    {
        // Attempt to register a new user with password length shorter than 8
        $response = $this->postJson('/api/auth/register', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'phone' => '+2349055667788',
            'email' => 'test@example.com',
            'password' => 'pass',
        ]);
        
        // Assert that the response status is 422
        $response->assertStatus(422);

        // Assert that the returned response structure matches the expected structure
        $response->assertJson([
            'status' => false,
            'message' => 'Request Failed',
            'errors' => [
                'password' => ['The password field must be at least 8 characters.'],
            ],
        ]);
    }

    /**
     * Test that a user cannot register with wrong email format
     */
    public function test_user_cannot_register_with_invalid_email()
    {
        // Attempt to register a user with invalid email
        $response = $this->postJson('/api/auth/register', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'phone' => '+2349055667788',
            'email' => 'example.com',
            'password' => 'password123',
        ]);
        
        // Assert that the response status is 422
        $response->assertStatus(422);

        // Assert that the returned response structure matches the expected structure
        $response->assertJson([
            'status' => false,
            'message' => 'Request Failed',
            'errors' => [
                'email' => ['The email field must be a valid email address.'],
            ],
        ]);
    }

    /**
     * Test that a user cannot use email address that already exists
     */
    public function test_user_cannot_register_with_existing_email(): void
    {
        $email = 'test@example.com';
        // Register a new user
        $response = $this->postJson('/api/auth/register', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'phone' => '+2349055667788',
            'email' => $email,
            'password' => 'password123',
        ]);

        // Attempt to register a new user with same email
        $response = $this->postJson('/api/auth/register', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'phone' => '+2349055667788',
            'email' => $email,
            'password' => 'password123'
        ]);

        // Assert that the response status is 422
        $response->assertStatus(422);

        // Assert that the returned response structure matches the expected structure
        $response->assertJson([
            'status' => false,
            'message' => 'Request Failed',
            'errors' => [
                'email' => ['The email has already been taken.'],
            ],
        ]);
    }

    public function test_user_cannot_register_with_invalid_phone_number(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'jane@example.com',
            'phone' => '1234567890',
            'password' => 'password123',
        ]);

        $response->assertStatus(422);

        $this->assertFalse($response->json('status'));
        $this->assertEquals('Request Failed', $response->json('message'));
        $response->assertJsonValidationErrors(['phone']);
    }

    public function test_registered_event_fired()
    {
        Event::fake();
        $response = $this->postJson('/api/auth/register', [
            'first_name' => 'John',
            'last_name'  => 'Doe',
            'email'      => 'john@example.com',
            'phone'      => '+2349055667788',
            'password'   => 'password123',
        ]);

        $response->assertStatus(201);

        // Assert that event was fired
        Event::assertDispatched(UserRegistered::class);
    }


    public function test_verification_email_is_sent_after_registration(): void
    {
        Mail::fake();

        $response = $this->postJson('/api/auth/register', [
            'first_name' => 'John',
            'last_name'  => 'Doe',
            'email'      => 'john@example.com',
            'phone'      => '+2349055667788',
            'password'   => 'password123',
        ]);

        $response->assertStatus(201);

        // Assert the email was sent to the correct user && email contains otp
        Mail::assertSent(EmailVerificationOtp::class, function ($mail) {
            return $mail->hasTo('john@example.com') &&
                $mail->user->email_verification_code !== null;
        });

        // Assert that mail was sent only once
        Mail::assertSent(EmailVerificationOtp::class, 1);
    }

    //test for too lengthy inputs
    //test for invalid input for names

}