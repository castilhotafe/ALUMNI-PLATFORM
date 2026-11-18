<?php

namespace Database\Factories;

use App\Models\EnrolmentListing;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EnrolmentListing>
 */
class EnrolmentListingFactory extends Factory
{
    protected $model = EnrolmentListing::class;

    public function definition(): array
    {
        return [
            'creator_user_id' => User::factory(),
            'course_title' => fake()->sentence(3),
            'tafe_provider' => 'North Metropolitan TAFE',
            'description' => fake()->paragraph(),
            'enrol_link' => fake()->url(),
        ];
    }
}
