<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Database\Factories\TaskFactory;
use Tests\TestCase;
use App\Models\User;

class TaskControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_get_all_tasks()
    {
        TaskFactory::new()->count(20)->create();

        $this->getJson('api/tasks')
            ->assertJsonStructure([
                'data' => [
                    [
                        'id',
                        'title',
                        'status',
                    ],
                ],
            ])
            ->assertJsonCount(15, 'data')
            ->assertJson([
                'meta' => [
                    'total' => 20,
                ],
            ]);
    }

    public function test_can_create_new_tasks()
    {
        $this->postJson('api/tasks', [
            'title' => "example task",
        ])->assertSuccessful()
            ->assertJson([
                'data' => [
                    'id' => 1,
                    'title' => 'example task',
                    'status' => false,
                ]
            ]);
    }

    public function test_user_can_login()
    {

        $this->postJson('api/users', [
            'email' => "test@gmail.com",
            'password' => '12345'
        ])->assertSuccessful()->dump()->assertJson([
            'data' => [
                'id' => 1,
                'email' => 'test@gmail.com',
                'password' => '12345',
            ]
        ]);;

    }

    public function test_user_is_requiered()
    {
        $this->postJson('api/users', [
            'email' => "test@gmail.com",
            'password' => "12345",
        ])->assertValid('email' | 'password');
    }

    public function test_task_is_incompleted_by_default()
    {
        $this->postJson('api/tasks', [
            'title' => "example task",
            'status' => true,
        ])->assertSuccessful()
            ->assertJson([
                'data' => [
                    'id' => 1,
                    'title' => 'example task',
                    'status' => false,
                ]
            ]);
    }

    public function test_task_title_is_requiered()
    {
        $this->postJson('api/tasks', [
            'title1' => "example task",
        ])->assertJsonValidationErrorFor('title');
    }

    public function test_can_update_current_task()
    {
        TaskFactory::new()->create([
            'title' => 'old title',
            'description' => 'hhhh',
            'status' => false,
        ]);

        $this->putJson('api/tasks/1', [
            'title' => "new title",
            'description' => 'hhhh',
            'status' => true,
        ])->assertSuccessful()
            ->assertJson([
                'data' => [
                    'id' => 1,
                    'title' => 'new title',
                    'description' => 'hhhh',
                    'status' => true,
                ]
            ]);
    }

    public function test_can_update_completed_task()
    {
        TaskFactory::new()->create([
            'title' => 'old title',
            'status' => true,
        ]);

        $this->putJson('api/tasks/1', [
            'title' => "new title",
            'status' => false,
        ])->assertSuccessful()
            ->assertJson([
                'data' => [
                    'id' => 1,
                    'title' => 'new title',
                    'status' => false,
                ]
            ]);
    }

    public function test_can_delete_current_task()
    {
        TaskFactory::new()->create([
            'title' => 'old title',
            'status' => false,
        ]);

        $this->deleteJson('api/tasks/1')
            ->assertSuccessful();

        $this->assertDatabaseCount('tasks', 0);
    }
}
