<?php

namespace Database\Factories;

use App\Models\JobListing;
use App\Models\JobListingProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<JobListingProfile>
 */
class JobListingProfileFactory extends Factory
{
    protected $model = JobListingProfile::class;

    public function definition(): array
    {
        return [
            'job_listing_id' => JobListing::factory(),
            'bio' => fake()->paragraph(),
            'details' => [fake()->jobTitle(), fake()->word()],
            'picture_url' => fake()->imageUrl(),
        ];
    }
}
