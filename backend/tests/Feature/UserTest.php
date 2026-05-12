<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->token = $this->user->createToken('test-token')->plainTextToken;
    }

    public function test_unauthenticated_user_cannot_view_profile(): void
    {
        $response = $this->getJson('/api/v1/users/me');

        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_view_their_profile(): void
    {
        $response = $this->withToken($this->token)
            ->getJson('/api/v1/users/me');

        $response->assertStatus(200);
        $response->assertJson([
            'id' => $this->user->id,
            'email' => $this->user->email,
            'username' => $this->user->username,
        ]);
    }

    public function test_user_can_update_own_profile(): void
    {
        $response = $this->withToken($this->token)
            ->patchJson('/api/v1/users/update', [
                'username' => 'UpdatedName',
                'email' => 'updated@example.com',
                'current_password' => 'password',
            ]);

        $response->assertStatus(200);
        $this->assertEquals('UpdatedName', $response->json('data.username'));
    }

    public function test_user_cannot_update_to_existing_email(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);

        $response = $this->withToken($this->token)
            ->patchJson('/api/v1/users/update', [
                'username' => 'SomeName',
                'email' => 'taken@example.com',
                'current_password' => 'password',
            ]);

        $response->assertStatus(422);
    }

    public function test_user_can_delete_own_account(): void
    {
        $response = $this->withToken($this->token)
            ->deleteJson("/api/v1/users/delete/{$this->user->id}");

        $response->assertStatus(200);
        $this->assertModelMissing($this->user);
    }

    public function test_user_cannot_delete_another_user(): void
    {
        $otherUser = User::factory()->create();

        $response = $this->withToken($this->token)
            ->deleteJson("/api/v1/users/delete/{$otherUser->id}");

        $response->assertStatus(403);
        $this->assertModelExists($otherUser);
    }

    public function test_unauthenticated_user_cannot_delete_account(): void
    {
        $otherUser = User::factory()->create();

        $response = $this->deleteJson("/api/v1/users/delete/{$otherUser->id}");

        $response->assertStatus(401);
        $this->assertModelExists($otherUser);
    }
}
