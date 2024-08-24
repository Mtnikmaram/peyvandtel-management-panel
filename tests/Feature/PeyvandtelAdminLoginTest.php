<?php

namespace Tests\Feature;

use App\Models\PeyvandtelAdmin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PeyvandtelAdminLoginTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_wrong_data_login_peyvandtel_admin(): void
    {
        $this
            ->post(route('peyvandtel.auth.login.login'), [
                "username" => 'wrong',
                "password" => 'wrong',
                "model" => "testFailedModel"
            ])
            ->assertNotFound();

        $this->assertDatabaseMissing('personal_access_tokens', ["tokenable_type" => PeyvandtelAdmin::class, "name" => "testFailedModel"]);
    }

    public function test_wrong_password_login_peyvandtel_admin(): void
    {
        $response = $this
            ->post(route('peyvandtel.auth.login.login'), [
                "username" => config('peyvandtelAdmin.credential.username'),
                "password" => 'wrong',
                "model" => "testFailedModel"
            ])
            ->assertUnprocessable();

        $this->assertArrayHasKey("errors", $response);
        $this->assertIsArray($response["errors"]);
        $this->assertDatabaseMissing('personal_access_tokens', ["tokenable_type" => PeyvandtelAdmin::class, "name" => "testFailedModel"]);
    }

    public function test_login_peyvandtel_admin(): void
    {
        $this->seed();

        $response = $this
            ->post(route('peyvandtel.auth.login.login'), [
                "username" => config('peyvandtelAdmin.credential.username'),
                "password" => config('peyvandtelAdmin.credential.password'),
                "model" => "testModel"
            ])
            ->assertSuccessful()
            ->json();

        $this->assertArrayHasKey("token", $response);
        $this->assertArrayHasKey("user", $response);
        $this->assertDatabaseHas('personal_access_tokens', ["tokenable_type" => PeyvandtelAdmin::class, "name" => "testModel"]);
    }
}
