<?php

namespace Tests\Feature;

use App\Models\PeyvandtelAdmin;
use App\Models\ServicePrice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Collection;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ServicePricesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();

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


    public function test_update_wrong_id()
    {
        $this
            ->putJson(route('peyvandtel.servicePrices.update', 0))
            ->assertNotFound();
    }

    public function test_update_validation()
    {
        // create 
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
        $servicePrice = ServicePrice::query()->first();


        //update
        $data = [
            "amount" => null,
            "setting" => 5,
        ];

        $this
            ->putJson(route('peyvandtel.servicePrices.update', $servicePrice->id), $data)
            ->assertUnprocessable()
            ->assertJsonValidationErrors(array_keys($data));

        $data = [
            "amount" => 'string_instead_of_int',
            "setting" => ['null_key' => ["key" => null, "value" => null]],
        ];

        $this->postJson(route('peyvandtel.servicePrices.store'), $data)
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['amount', 'setting.null_key.key', 'setting.null_key.value']);


        //assert
        $this->assertDatabaseMissing('service_prices', ["service_id" => $servicePrice->service_id, "amount" => "string_instead_of_int"]);
    }

    public function test_update_sahab_part_ai_speech_to_text_price_service_validator()
    {
        // create 
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
        $servicePrice = ServicePrice::query()->where('service_id', "SahabPartAISpeechToText")->first();

        $data = [
            "amount" => $servicePrice->amount + 20,
            "setting" => null
        ];

        $this->putJson(route('peyvandtel.servicePrices.update', $servicePrice->id), $data)
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['setting']);

        $data["setting"] = [["key" => "not_the_valid_key_for_this_service", "value" => 15]];

        $this->putJson(route('peyvandtel.servicePrices.update', $servicePrice->id), $data)
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['setting']);

        $this->assertDatabaseMissing('service_prices', ["service_id" => $servicePrice->service_id, "setting" => json_encode($data["setting"])]);
    }

    public function test_update_sahab_part_ai_speech_to_text_price()
    {
        // create 
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

        $servicePrice = ServicePrice::query()->where('service_id', "SahabPartAISpeechToText")->first();
        $setting = collect($servicePrice->setting);

        $data = [
            "amount" => $servicePrice->amount + 20,
            "setting" => [
                [
                    "key" => "each_second",
                    "value" => $setting->where('key', 'each_second')->first()["value"] + 20
                ]
            ]
        ];

        $this->putJson(route('peyvandtel.servicePrices.update', $servicePrice->id), $data)
            ->assertNoContent();

        $this->assertDatabaseHas('service_prices', ["id" => $servicePrice->id, "service_id" => $servicePrice->service_id, "amount" => $data['amount'], 'setting' => json_encode($data['setting'])]);
    }

    public function test_delete_service_price()
    {
        // create 
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
        $servicePrice = ServicePrice::query()->first();

        $this->deleteJson(route('peyvandtel.servicePrices.destroy', $servicePrice->id))
            ->assertNoContent();

        $this->assertDatabaseMissing('service_prices', ["id" => $servicePrice->id]);
    }
}
