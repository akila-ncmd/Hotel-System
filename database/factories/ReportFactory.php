<?php

namespace Database\Factories;

use App\Models\Branch;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Report>
 */
class ReportFactory extends Factory
{
    /**
     * A saved nightly snapshot for one branch on one date.
     */
    public function definition(): array
    {
        return [
            'branch_id' => Branch::factory(),
            'report_date' => Carbon::yesterday()->toDateString(),
            'total_occupancy' => fake()->numberBetween(0, 50),
            'total_revenue' => fake()->randomFloat(2, 0, 5000),
            'no_show_count' => fake()->numberBetween(0, 3),
        ];
    }
}
