<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_with_valid_data(): void
    {
        $response = $this->postJson('/api/register', [
            'username' => 'newuser',
            'email' => 'new@example.com',
            'password' => 'StrongP@ss1',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Registered user successfully',
            ]);
    }

    public function test_registration_returns_generic_error_for_duplicate_email(): void
    {
        User::factory()->create(['email' => 'existing@example.com']);

        $response = $this->postJson('/api/register', [
            'username' => 'another',
            'email' => 'existing@example.com',
            'password' => 'StrongP@ss1',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Registration failed. Please check your input.',
            ]);

        $response->assertJsonMissingPath('errors.email');
    }

    public function test_registration_does_not_return_user_data(): void
    {
        $response = $this->postJson('/api/register', [
            'username' => 'newuser',
            'email' => 'new@example.com',
            'password' => 'StrongP@ss1',
        ]);

        $response->assertJsonMissingPath('data');
        $response->assertJsonMissingPath('data.id');
        $response->assertJsonMissingPath('data.email');
    }

    public function test_registration_rejects_short_password(): void
    {
        $response = $this->postJson('/api/register', [
            'username' => 'newuser',
            'email' => 'new@example.com',
            'password' => 'short',
        ]);

        $response->assertStatus(422);
    }

    public function test_registration_rejects_missing_fields(): void
    {
        $response = $this->postJson('/api/register', []);

        $response->assertStatus(422);
    }

    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Logged in successfully',
            ]);

        $response->assertJsonStructure(['token', 'token_type']);
        $this->assertEquals('Bearer', $response['token_type']);
    }

    public function test_login_fails_with_wrong_password(): void
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('correct-password'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(422);
    }

    public function test_login_fails_with_nonexistent_email(): void
    {
        $response = $this->postJson('/api/login', [
            'email' => 'nobody@example.com',
            'password' => 'somepassword',
        ]);

        $response->assertStatus(422);
    }

    public function test_login_error_is_identical_for_wrong_email_and_wrong_password(): void
    {
        User::factory()->create([
            'email' => 'exists@example.com',
            'password' => bcrypt('real-password'),
        ]);

        $wrongEmail = $this->postJson('/api/login', [
            'email' => 'nobody@example.com',
            'password' => 'somepassword',
        ]);

        $wrongPassword = $this->postJson('/api/login', [
            'email' => 'exists@example.com',
            'password' => 'wrong-password',
        ]);

        $this->assertEquals(
            $wrongEmail->json('message'),
            $wrongPassword->json('message')
        );

        $this->assertEquals(
            $wrongEmail->json('errors.email.0'),
            $wrongPassword->json('errors.email.0')
        );
    }
}
