<?php

namespace Tests\Feature;

use App\Models\PeyvandtelAdmin;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

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

    
}
