<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'first_name' => fake()->firstName(), 
            'last_name' => fake()->lastName(),   
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'phone_number' => fake()->unique()->numerify('09#########'),
            // 'points' => fake()->numberBetween(0, 100),
            // 'trust_score' => fake()->numberBetween(60, 100),
            'type' => fake()->randomElement(['respondent', 'researcher']), // Default to respondent or researcher
            // 'institution_id' => null, // You can set this specifically in your seeder or leave it null/set a default
            // 'profile_photo_path' => null, // Default to null or set a fake image path
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
