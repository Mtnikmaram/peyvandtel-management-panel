<?php

namespace Tests\Feature\User;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private string $password = "testPassword";

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();

        $this->user = User::factory()
            ->create([
                "username" => "mahdi",
                "name" => "مهدی آب‌باریکی",
                "phone" => "09335012118",
                "password" => $this->password,
                "credit_threshold" => 150000
            ])
            ->refresh();
    }

    public function test_wrong_data_login_user_validations(): void
    {
        $this
            ->postJson(route('user.auth.login.login'), [
                "username" => null,
                "password" => null,
                "model" => null
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(["username", "password", "model"]);
    }

    public function test_wrong_data_login_user(): void
    {
        $this
            ->postJson(route('user.auth.login.login'), [
                "username" => 'wrong',
                "password" => 'wrong',
                "model" => "testFailedModel"
            ])
            ->assertUnprocessable();

        $this->assertDatabaseMissing('personal_access_tokens', ["tokenable_type" => User::class, "name" => "testFailedModel"]);
    }

    public function test_wrong_password_login_user(): void
    {
        $this
            ->postJson(route('user.auth.login.login'), [
                "username" => $this->user->username,
                "password" => 'wrong',
                "model" => "testFailedModel"
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(["credentials"]);

        $this->assertDatabaseMissing('personal_access_tokens', ["tokenable_type" => User::class, "name" => "testFailedModel"]);
    }

    public function test_login_user(): void
    {
        $this->seed();

        $this
            ->postJson(route('user.auth.login.login'), [
                "username" => $this->user->username,
                "password" => $this->password,
                "model" => "testModel"
            ])
            ->assertOk()
            ->assertJsonStructure(["token"]);

        $this->assertDatabaseHas('personal_access_tokens', ["tokenable_type" => User::class, "name" => "testModel"]);


        $this
            ->postJson(route('user.auth.login.login'), [
                "username" => $this->user->username,
                "password" => $this->password,
                "model" => "testModel"
            ])
            ->assertOk()
            ->assertJsonStructure(["token"]);

        $count = DB::table('personal_access_tokens')
            ->where('tokenable_type', User::class)
            ->where('name', 'testModel')
            ->count();
        $this->assertEquals(1, $count); // token with same model must be deleted and another one must be created
    }
}
