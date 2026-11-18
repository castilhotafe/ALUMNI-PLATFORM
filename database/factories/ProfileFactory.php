<?php

namespace Database\Factories;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Profile>
 */
class ProfileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'bio' => fake()->paragraph(),
            'details' => [
                fake()->jobTitle(),
                fake()->word(),
                fake()->city(),
            ],
            'resume_link' => fake()->optional()->url(),
            'picture_url' => fake()->optional()->imageUrl(400, 400, 'people'),
        ];
    }
}
