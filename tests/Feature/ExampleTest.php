<?php

namespace Tests\Feature;

use Database\Seeders\BudgetAppSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     */
    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get('/');
        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_view_dashboard(): void
    {
        $this->seed(BudgetAppSeeder::class);
        $user = \App\Models\User::first();

        $response = $this->actingAs($user)->get('/');
        $response->assertStatus(200);
    }
}
