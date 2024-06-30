<?php

namespace Tests\Feature;

use App\Models\PeyvandtelAdmin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Collection;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ServicePricesTest extends TestCase
{
    use RefreshDatabase;

    private Collection $servicePrices;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();

        $this->servicePrices = collect();

        Sanctum::actingAs(PeyvandtelAdmin::first());
    }

    public function test_store_validation()
    {
        $data = [
            "serviceId" => "id_that_does_not_exists",
            "amount" => 'string_instead_of_int',
            "setting" => 5,
        ];

        $this->postJson(route('peyvandtel.servicePrices.store'), $data)
            ->assertUnprocessable()
            ->assertJsonValidationErrors(array_keys($data));

        $data = [
            "serviceId" => "id_that_does_not_exists",
            "amount" => 'string_instead_of_int',
            "setting" => ['null_key' => ["key" => null, "value" => null]],
        ];

        $this->postJson(route('peyvandtel.servicePrices.store'), $data)
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['serviceId', 'amount', 'setting.null_key.key', 'setting.null_key.value']);

        $this->assertDatabaseMissing('service_prices', ["service_id" => $data['serviceId']]);
    }

    public function test_store_sahab_part_ai_speech_to_text_price_service_validator()
    {
        $data = [
            "serviceId" => "SahabPartAISpeechToText",
            "amount" => 80,
            "setting" => null
        ];

        $this->postJson(route('peyvandtel.servicePrices.store'), $data)
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['setting']);

        $data["setting"] = [["key" => "not_the_valid_key_for_this_service", "value" => 15]];

        $this->postJson(route('peyvandtel.servicePrices.store'), $data)
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['setting']);

        $this->assertDatabaseMissing('service_prices', ["service_id" => $data['serviceId']]);
    }

    public function test_store_sahab_part_ai_speech_to_text_price()
    {
        $data = [
            "serviceId" => "SahabPartAISpeechToText",
            "amount" => 80,
            "setting" => [
                [
                    "key" => "each_second",
                    "value" => 15
                ]
            ]
        ];

        $this->postJson(route('peyvandtel.servicePrices.store'), $data)
            ->assertCreated();

        $this->assertDatabaseHas('service_prices', ["service_id" => $data['serviceId'], "amount" => $data['amount'], 'setting' => json_encode($data['setting'])]);
    }

    public function test_service_prices_index(): void
    {
        $response = $this
            ->getJson(route('peyvandtel.servicePrices.index'))
            ->assertSuccessful()
            ->assertJsonStructure([
                "prices" => [
                    "data",
                    "total",
                    "current_page",
                    "last_page"
                ]
            ])
            ->json();

        $this->assertDatabaseCount('service_prices', $response['prices']['total']);
    }
}
