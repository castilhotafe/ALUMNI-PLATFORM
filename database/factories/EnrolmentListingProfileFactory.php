<?php

namespace Database\Factories;

use App\Models\EnrolmentListing;
use App\Models\EnrolmentListingProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EnrolmentListingProfile>
 */
class EnrolmentListingProfileFactory extends Factory
{
    protected $model = EnrolmentListingProfile::class;

    public function definition(): array
    {
        return [
            'enrolment_listing_id' => EnrolmentListing::factory(),
            'bio' => fake()->paragraph(),
            'details' => [fake()->word(), fake()->word()],
            'picture_url' => fake()->imageUrl(),
        ];
    }
}
