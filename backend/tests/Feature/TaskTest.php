<?php

namespace Tests\Feature;

use App\Models\Tasks;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskTest extends TestCase
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

    public function test_unauthenticated_user_cannot_list_tasks(): void
    {
        $response = $this->getJson('/api/v1/tasks/get');

        $response->assertStatus(401);
    }

    public function test_authenticated_user_sees_only_their_tasks(): void
    {
        $otherUser = User::factory()->create();
        Tasks::factory()->count(3)->create(['user_id' => $this->user->id]);
        Tasks::factory()->count(2)->create(['user_id' => $otherUser->id]);

        $response = $this->withToken($this->token)
            ->getJson('/api/v1/tasks/get');

        $response->assertStatus(200);
        $response->assertJsonCount(3, 'data');
    }

    public function test_list_tasks_can_filter_by_valid_status(): void
    {
        Tasks::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'pending',
        ]);
        Tasks::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'completed',
        ]);
        Tasks::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'incomplete',
        ]);

        $response = $this->withToken($this->token)
            ->getJson('/api/v1/tasks/get?status=completed');

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $this->assertEquals('completed', $response->json('data.0.status'));
    }

    public function test_list_tasks_rejects_invalid_status(): void
    {
        $response = $this->withToken($this->token)
            ->getJson('/api/v1/tasks/get?status=invalid_status');

        $response->assertStatus(422);
    }

    public function test_list_tasks_search_escapes_wildcards(): void
    {
        Tasks::factory()->create([
            'user_id' => $this->user->id,
            'title' => 'meeting notes',
        ]);
        Tasks::factory()->create([
            'user_id' => $this->user->id,
            'title' => '100% completion',
        ]);

        $response = $this->withToken($this->token)
            ->getJson('/api/v1/tasks/get?search=%25');

        $response->assertStatus(200);
    }

    public function test_unauthenticated_user_cannot_create_task(): void
    {
        $response = $this->postJson('/api/v1/tasks/create', [
            'title' => 'New Task',
        ]);

        $response->assertStatus(401);
    }

    public function test_user_can_create_task(): void
    {
        $response = $this->withToken($this->token)
            ->postJson('/api/v1/tasks/create', [
                'title' => 'My Task',
                'description' => 'Task description',
            ]);

        $response->assertStatus(201);
        $response->assertJson([
            'data' => [
                'title' => 'My Task',
                'description' => 'Task description',
                'status' => 'pending',
            ],
        ]);

        $this->assertDatabaseHas('tasks', [
            'title' => 'My Task',
            'user_id' => $this->user->id,
        ]);
    }

    public function test_create_task_requires_title(): void
    {
        $response = $this->withToken($this->token)
            ->postJson('/api/v1/tasks/create', []);

        $response->assertStatus(422);
    }

    public function test_unauthenticated_user_cannot_update_task(): void
    {
        $task = Tasks::factory()->create(['user_id' => $this->user->id]);

        $response = $this->patchJson("/api/v1/tasks/update/{$task->id}", [
            'title' => 'Hacked',
        ]);

        $response->assertStatus(401);
    }

    public function test_task_owner_can_update_task(): void
    {
        $task = Tasks::factory()->create([
            'user_id' => $this->user->id,
            'title' => 'Original Title',
            'status' => 'pending',
        ]);

        $response = $this->withToken($this->token)
            ->patchJson("/api/v1/tasks/update/{$task->id}", [
                'title' => 'Updated Title',
                'status' => 'completed',
            ]);

        $response->assertStatus(200);
        $this->assertEquals('Updated Title', $response->json('data.title'));
        $this->assertEquals('completed', $response->json('data.status'));
    }

    public function test_non_owner_cannot_update_task(): void
    {
        $otherUser = User::factory()->create();
        $task = Tasks::factory()->create([
            'user_id' => $otherUser->id,
            'title' => 'Other\'s Task',
        ]);

        $response = $this->withToken($this->token)
            ->patchJson("/api/v1/tasks/update/{$task->id}", [
                'title' => 'My Update',
            ]);

        $response->assertStatus(403);
    }

    public function test_update_rejects_invalid_status(): void
    {
        $task = Tasks::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $response = $this->withToken($this->token)
            ->patchJson("/api/v1/tasks/update/{$task->id}", [
                'status' => 'invalid_status',
            ]);

        $response->assertStatus(422);
    }

    public function test_task_owner_can_delete_task(): void
    {
        $task = Tasks::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $response = $this->withToken($this->token)
            ->deleteJson("/api/v1/tasks/delete/{$task->id}");

        $response->assertStatus(200);
        $this->assertModelMissing($task);
    }

    public function test_non_owner_cannot_delete_task(): void
    {
        $otherUser = User::factory()->create();
        $task = Tasks::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        $response = $this->withToken($this->token)
            ->deleteJson("/api/v1/tasks/delete/{$task->id}");

        $response->assertStatus(403);
    }
}
