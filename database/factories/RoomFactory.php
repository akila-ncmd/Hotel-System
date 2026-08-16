<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\RoomType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Room>
 */
class RoomFactory extends Factory
{
    /**
     * A bookable physical room.
     *
     * `status` is a right-now flag only — it says nothing about future dates.
     * Date-aware availability comes from App\Services\RoomAvailability.
     */
    public function definition(): array
    {
        return [
            'branch_id' => Branch::factory(),
            'room_type_id' => RoomType::factory(),
            'room_number' => (string) fake()->unique()->numberBetween(100, 9999),
            'status' => 'available',
        ];
    }

    /** Out of service — excluded from bookable capacity. */
    public function maintenance(): static
    {
        return $this->state(fn () => ['status' => 'maintenance']);
    }
}
