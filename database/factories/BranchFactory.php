<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Branch>
 */
class BranchFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->city() . ' Branch',
            'address' => fake()->address(),
            'contact_number' => fake()->numerify('0##########'),
        ];
    }
}
