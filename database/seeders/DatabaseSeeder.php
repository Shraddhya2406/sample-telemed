<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\UserSeeder;
use Database\Seeders\AppointmentSeeder;
use Database\Seeders\HealthQuestionSeeder;
use Database\Seeders\HealthOptionSeeder;
use Database\Seeders\MedicineRecommendationSeeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            UserSeeder::class,
            AppointmentSeeder::class,
            HealthQuestionSeeder::class,
            HealthOptionSeeder::class,
            MedicineRecommendationSeeder::class,
        ]);
    }
}
