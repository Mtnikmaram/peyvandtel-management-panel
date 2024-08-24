<?php

namespace Database\Seeders;

use App\Models\PeyvandtelAdmin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class PeyvandtelAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        PeyvandtelAdmin::query()
            ->updateOrCreate(
                [
                    "username" => config('peyvandtelAdmin.credential.username')
                ],
                [
                    "name" => "مدیریت نرم‌افزار",
                    "username" => config('peyvandtelAdmin.credential.username'),
                    "password" => Hash::make(config('peyvandtelAdmin.credential.password'))
                ]
            );
    }
}
