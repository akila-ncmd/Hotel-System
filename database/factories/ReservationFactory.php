<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\RoomType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Reservation>
 */
class ReservationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * A future-dated, pending room booking. Note that room_id is deliberately null —
     * a physical room is only assigned at check-in.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $checkIn = Carbon::tomorrow()->addDays(fake()->numberBetween(0, 30));

        return [
            'user_id' => User::factory(),
            'branch_id' => Branch::factory(),
            'room_type_id' => RoomType::factory(),
            'room_id' => null,
            'check_in_date' => $checkIn,
            'check_out_date' => $checkIn->copy()->addDays(fake()->numberBetween(1, 5)),
            'duration_type' => null,
            'duration_value' => null,
            'number_of_occupants' => 1,
            'status' => 'pending',
            'credit_card_details' => null,
        ];
    }

    /**
     * A residential-suite booking, billed by duration rather than by night.
     */
    public function suite(): static
    {
        return $this->state(function (array $attributes) {
            $checkIn = Carbon::parse($attributes['check_in_date']);
            $weeks = fake()->numberBetween(1, 3);

            return [
                'room_type_id' => RoomType::factory()->suite(),
                'check_out_date' => $checkIn->copy()->addWeeks($weeks),
                'duration_type' => 'weeks',
                'duration_value' => $weeks,
            ];
        });
    }
}
