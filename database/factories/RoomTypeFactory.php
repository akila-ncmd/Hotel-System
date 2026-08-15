<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RoomType>
 */
class RoomTypeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * Defaults to a non-suite room priced per night. Use the suite() state for
     * residential suites, which are billed weekly or monthly instead.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'Room Type ' . fake()->unique()->numberBetween(1000, 9999),
            'description' => fake()->sentence(),
            'price_per_night' => fake()->randomFloat(2, 40, 250),
            'weekly_rate' => null,
            'monthly_rate' => null,
            'max_occupants' => fake()->numberBetween(1, 4),
            'is_suite' => false,
        ];
    }

    /**
     * A residential suite: no nightly rate, billed by week or month.
     */
    public function suite(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Suite ' . fake()->unique()->numberBetween(1000, 9999),
            'price_per_night' => null,
            'weekly_rate' => fake()->randomFloat(2, 800, 1500),
            'monthly_rate' => fake()->randomFloat(2, 3000, 5000),
            'max_occupants' => fake()->numberBetween(2, 6),
            'is_suite' => true,
        ]);
    }
}
