<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Miguel Inciong',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'type' => 'researcher',
        ]);

        User::factory()->create([
            'name' => 'Dion Marmon',
            'email' => 'test1@example.com',
            'password' => Hash::make('password123'),
            'type' => 'researcher',
        ]);

        User::factory()->create([
            'name' => 'Kurt Aquino',
            'email' => 'test2@example.com',
            'password' => Hash::make('password123'),
            'type' => 'researcher',
        ]);

        User::factory()->create([
            'name' => 'Rence Baldeo',
            'email' => 'test3@example.com',
            'password' => Hash::make('password123'),
            'type' => 'researcher',
        ]);

        $this->call([
            TagCategorySeeder::class,
            TagSeeder::class,
            SurveySeeder::class,
        ]);
        
    }
}
