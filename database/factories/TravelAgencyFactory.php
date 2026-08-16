<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TravelAgency>
 */
class TravelAgencyFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->company(),
            'contact_email' => fake()->unique()->companyEmail(),
            'contact_number' => fake()->numerify('0##########'),
            'is_verified' => true,
        ];
    }
}
