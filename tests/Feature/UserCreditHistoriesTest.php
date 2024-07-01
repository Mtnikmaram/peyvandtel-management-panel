<?php

namespace Tests\Feature;

use App\Jobs\SendSmsJob;
use App\Models\PeyvandtelAdmin;
use App\Models\User;
use Database\Seeders\PeyvandtelAdminSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use Illuminate\Support\Facades\Queue;

class UserCreditHistoriesTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(class: PeyvandtelAdminSeeder::class);
        Sanctum::actingAs(PeyvandtelAdmin::first());

        $this->user = User::factory()
            ->create([
                "username" => "mahdi",
                "name" => "مهدی آب‌باریکی",
                "phone" => "09335012118",
                "credit_threshold" => 150000
            ])
            ->refresh();
    }

    public function test_user_credit_threshold_send_sms()
    {
        Queue::fake();

        $newCredit = $this->user->credit_threshold * 0.5; // the updated credit will be less than credit_threshold
        $creditDescription = "test sms for credit threshold";

        $this
            ->patchJson(
                route('peyvandtel.users.update', $this->user->id),
                [
                    "credit" => $newCredit,
                    "credit_description" => $creditDescription
                ]
            )
            ->assertNoContent();

        $this->assertDatabaseHas('user_credit_histories', ["user_id" => $this->user->id, "amount" => $newCredit, "description" => $creditDescription]);
        Queue::assertPushedOn('sms', SendSmsJob::class); // the sms for credit threshold will be sent with the jobs. it also tests sendTemplateSms of SmsProvider
    }
}
