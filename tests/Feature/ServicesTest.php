<?php

namespace Tests\Feature;

use App\Models\PeyvandtelAdmin;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use Illuminate\Support\Str;

class ServicesTest extends TestCase
{
    use RefreshDatabase;

    private array $services;

    protected function setUp(): void
    {
        parent::setUp();

        $this->services = Service::$services;

        $this->seed();
        Sanctum::actingAs(PeyvandtelAdmin::first());
    }

    /**
     * A basic feature test example.
     */
    public function test_fetch_services_paginated_list(): void
    {
        $response = $this
            ->get(route('peyvandtel.services.index'))
            ->assertSuccessful()
            ->assertJsonStructure([
                "services" => [
                    "data",
                    "current_page",
                    "total"
                ]
            ]);

        $this->assertEquals(count($this->services), $response["services"]["total"]);
    }


    public function test_set_credential_by_token()
    {
        $service = Service::first();
        $token = Str::random(20);

        $this
            ->put(
                route('peyvandtel.services.setTokenCredential', $service->id),
                [
                    "token" => $token
                ]
            )
            ->assertNoContent();

        $service->refresh();
        $this->assertTrue($service->has_credential);
        $this->assertEquals($token, $service->credential);
        $this->assertDatabaseMissing('services', ["id" => $service->id, "credential" => $token]); //token ust be encrypted
    }

    public function test_set_credential_by_token_validation()
    {
        $service = Service::first();
        $this
            ->putJson(
                route('peyvandtel.services.setTokenCredential', $service->id),
                [
                    "token" => null
                ]
            )
            ->assertUnprocessable()
            ->assertJsonValidationErrors('token');
    }

    public function test_set_credential_by_username_password()
    {
        $service = Service::first();
        $username = Str::random(5);
        $password = Str::password();

        $this
            ->putJson(
                route('peyvandtel.services.setUsernamePasswordCredential', $service->id),
                [
                    "username" => $username,
                    "password" => $password,
                ]
            )
            ->assertNoContent();

        $service->refresh();
        $this->assertTrue($service->has_credential);

        $splitted = Service::splitUsernameAndPassword($service->credential); // username and password can be restored after encryption
        $this->assertEquals($username, $splitted["username"]);
        $this->assertEquals($password, $splitted["password"]);
    }

    public function test_set_credential_by_username_password_validation()
    {
        $service = Service::first();

        $this
            ->putJson(
                route('peyvandtel.services.setUsernamePasswordCredential', $service->id),
                [
                    "username" => null,
                    "password" => null,
                ]
            )
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['username', 'password']);
    }

    public function test_toggle_active_state()
    {
        $service = Service::first();

        $response = $this
            ->putJson(route('peyvandtel.services.toggleActive', $service->id))
            ->assertSuccessful()
            ->assertJsonStructure(['newState'])
            ->json();

        $this->assertNotEquals($response["newState"], $service->active);
    }
}
