<?php

namespace Database\Factories;

use App\Models\ResearchPaper;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ResearchPaper>
 */
class ResearchPaperFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(),
            'abstract' => $this->faker->paragraph(),
            'doi' => $this->faker->unique()->regexify('10\.\d{4,9}/[-._;()/:A-Z0-9]+'),
            'pdf_url' => $this->faker->url(),
            'visibility' => $this->faker->randomElement(['public', 'private']),
        ];
    }
}
