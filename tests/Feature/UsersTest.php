<?php

namespace Tests\Feature;

use App\Models\PeyvandtelAdmin;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UsersTest extends TestCase
{
    use RefreshDatabase;

    private Collection $users;

    protected function setUp(): void
    {
        parent::setUp();

        User::unguard();

        $this->users = User::factory()->count(10)->create();
        $this->users->push(
            User::factory()->create([
                "username" => "mahdi",
                "phone" => "09335012118",
                "name" => "مهدی آب‌باریکی",
                "credit" => 500000
            ])
        );

        $this->seed();
        Sanctum::actingAs(PeyvandtelAdmin::first());
    }


    /**
     * A basic feature test example.
     */
    public function test_fetch_users_paginated_list(): void
    {
        $response = $this
            ->get(route('peyvandtel.users.index'))
            ->assertSuccessful()
            ->assertJsonStructure([
                "users" => [
                    "data",
                    "current_page",
                    "total"
                ]
            ]);

        $this->assertEquals($this->users->count(), $response["users"]["total"]);
    }

    public function test_fetch_users_paginated_list_with_filters(): void
    {
        //phone
        $response = $this
            ->get(route('peyvandtel.users.index') . "?phone=35012118")
            ->assertSuccessful()
            ->json();

        $count = $this->users->filter(function ($q) {
            return Str::contains($q->phone, '35012118');
        })->count();
        $this->assertEquals($count, $response["users"]["total"]);

        //username
        $response = $this
            ->get(route('peyvandtel.users.index') . "?username=hadi")
            ->assertSuccessful()
            ->json();

        $count = $this->users->filter(function ($q) {
            return Str::contains($q->username, 'hadi');
        })->count();
        $this->assertEquals($count, $response["users"]["total"]);

        //name
        $response = $this
            ->get(route('peyvandtel.users.index') . "?name=مهدی")
            ->assertSuccessful()
            ->json();

        $count = $this->users->filter(function ($q) {
            return Str::contains($q->name, 'مهدی');
        })->count();
        $this->assertEquals($count, $response["users"]["total"]);

        //maxCredit
        $response = $this
            ->get(route('peyvandtel.users.index') . "?maxCredit=1000000")
            ->assertSuccessful()
            ->json();

        $count = $this->users->filter(function ($q) {
            return $q->credit <= 1000000;
        })->count();
        $this->assertEquals($count, $response["users"]["total"]);
    }

    public function test_store_new_user()
    {
        $data = [
            "username" => "newUser",
            "password" => "Password2024",
            "password_confirmation" => "Password2024",
            "phone" => "09123456789",
            "name" => "کاربر جدید",
            "credit_threshold" => 800000
        ];
        $this
            ->postJson(
                route('peyvandtel.users.store'),
                $data
            )
            ->assertCreated();

        $this->assertDatabaseCount('users', $this->users->count() + 1);
        $this->assertDatabaseHas('users', ["username" => $data["username"], "phone" => $data["phone"]]);

        $data['credit'] = 0;
        $this->users->push($data);

        $data = [
            "username" => "newUserWithoutPassword",
            "phone" => "09123456987",
            "name" => "کاربر جدید با پسوورد خالی",
            "credit_threshold" => 900000
        ];
        $response = $this
            ->post(
                route('peyvandtel.users.store'),
                $data
            )
            ->assertSuccessful()
            ->assertJsonStructure([
                "password"
            ]);

        $this->assertDatabaseCount('users', $this->users->count() + 1);
        $this->assertDatabaseHas('users', ["username" => $data["username"], "phone" => $data["phone"]]);

        $data['credit'] = 0;
        $data['password'] = $response["password"];
        $this->users->push($data);
    }

    public function test_store_validations()
    {
        $data = [
            "username" => $this->users->first()->username,
            "password" => "password2024",
            // "password_confirmation" => "password2024",
            "phone" => $this->users->first()->phone,
            "name" => "",
            "credit_threshold" => 0
        ];

        $this
            ->postJson(
                route('peyvandtel.users.store'),
                $data
            )
            ->assertUnprocessable()
            ->assertJsonValidationErrors(array_keys($data));
    }

    public function test_user_update()
    {
        $user = $this->users->first();
        $userId = $user->id;

        $data = [
            "username" => fake()->unique()->userName(),
            "password" => "Password2024",
            "name" => fake()->name(),
            "phone" => fake()->unique()->mobileNumber(),
            "credit" => $user->credit + 1000,
            "credit_threshold" => 150000
        ];

        foreach ($data as $key => $value) {
            $res = [$key => $value];
            if ($key == "password")
                $res["password_confirmation"] = $value;

            $this
                ->patch(route('peyvandtel.users.update', $userId), $res)
                ->assertNoContent();
        }

        unset($data['password']);
        $this->assertDatabaseHas('users', ["id" => $userId, ...$data]);
        $this->assertDatabaseMissing('users', ["id" => $userId, "password" => $user->password]);
        $this->assertDatabaseCount('users', $this->users->count());
    }

    public function test_user_show()
    {
        $user = $this->users->first();
        $userId = $user->id;

        $this->get(route('peyvandtel.users.show', $userId))
            ->assertOk()
            ->assertJsonStructure([
                "user" => [
                    "id",
                    "username",
                    "phone",
                    "name",
                    "credit",
                    "credit_threshold",
                    "created_at",
                    "updated_at",
                ]
            ]);
    }
}
