<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        Admin::firstOrCreate(
            ['email' => 'support@lab.local'],
            [
                'name'      => 'الدعم الفني',
                'phone'     => '07700000000',
                'password'  => bcrypt('changeme123'),
                'role'      => 'super_admin',
                'is_active' => true,
            ]
        );

        User::firstOrCreate(
            ['email' => 'support@lab.local'],
            [
                'name'                 => 'الدعم الفني',
                'phone'                => '07700000000',
                'password'             => bcrypt('changeme123'),
                'role'                 => 'admin',
                'is_profile_completed' => true,
                'is_active'            => true,
                'agreed_to_terms'      => true,
            ]
        );

        User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name'     => 'Test User',
                'password' => bcrypt('password'),
            ]
        );

        $this->call(MedicalDictionarySeeder::class);
        $this->call(SampleAndTubeTypeSeeder::class);
        $this->call(CouponSeeder::class);
    }
}
