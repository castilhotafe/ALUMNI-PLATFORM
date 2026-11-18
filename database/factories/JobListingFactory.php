<?php

namespace Database\Factories;

use App\Models\JobListing;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<JobListing>
 */
class JobListingFactory extends Factory
{
    protected $model = JobListing::class;

    public function definition(): array
    {
        return [
            'creator_user_id' => User::factory(),
            'title' => fake()->jobTitle(),
            'company' => fake()->company(),
            'description' => fake()->paragraph(),
            'apply_link' => fake()->url(),
        ];
    }
}
