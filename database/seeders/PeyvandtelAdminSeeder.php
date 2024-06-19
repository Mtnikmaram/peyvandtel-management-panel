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
            ->create([
                "name" => "مدیریت نرم‌افزار",
                "username" => env('PEYVANDTEL_ADMIN_USERNAME'),
                "password" => Hash::make(env('PEYVANDTEL_ADMIN_PASSWORD'))
            ]);
    }
}
