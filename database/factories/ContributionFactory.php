<?php

namespace Database\Factories;

use App\Models\Contribution;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Contribution>
 */
class ContributionFactory extends Factory
{
    protected $model = Contribution::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'amount' => fake()->numberBetween(10, 100),
            'reason' => fake()->sentence(),
            'reference_type' => null,
            'reference_id' => null,
            'admin_id' => null,
        ];
    }
}
