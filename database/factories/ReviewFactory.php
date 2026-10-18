<?php

namespace Database\Factories;

use App\Models\Review;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Review>
 */
class ReviewFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'content'      => fake()->realText(rand(100, 500)),
            'rating'       => rand(1, 5),
            'status'       => 'active',
            'published_at' => now(),
        ];
    }
}
