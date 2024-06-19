<?php

namespace Tests\Feature;

use App\Models\PeyvandtelAdmin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PeyvandtelAdminLoginTest extends TestCase
{
    use RefreshDatabase;


    /**
     * A basic feature test example.
     */
    public function test_login_peyvandtel_admin(): void
    {
        $this->seed();

        $response = $this
            ->post(route('peyvandtel.auth.login.login'), [
                "username" => env('PEYVANDTEL_ADMIN_USERNAME'),
                "password" => env("PEYVANDTEL_ADMIN_PASSWORD"),
                "model" => "testModel"
            ])
            ->assertSuccessful()
            ->json();

        $this->assertArrayHasKey("token", $response);
        $this->assertArrayHasKey("user", $response);
        $this->assertDatabaseHas('personal_access_tokens', ["tokenable_type" => PeyvandtelAdmin::class, "name" => "testModel"]);
    }
}
