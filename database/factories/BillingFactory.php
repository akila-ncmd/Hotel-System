<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Billing>
 */
class BillingFactory extends Factory
{
    /**
     * A folio with a room charge and no incidentals.
     *
     * The five *_charges columns are the system's fixed charge buckets; see
     * docs/gap-analysis.md §5 for why a folio-line table would be better.
     */
    public function definition(): array
    {
        return [
            'reservation_id' => Reservation::factory(),
            'user_id' => User::factory(),
            'branch_id' => Branch::factory(),
            'total_amount' => fake()->randomFloat(2, 50, 500),
            'payment_method' => null,
            'payment_status' => 'pending',
            'restaurant_charges' => 0,
            'room_service_charges' => 0,
            'laundry_charges' => 0,
            'telephone_charges' => 0,
            'club_facility_charges' => 0,
        ];
    }

    /** Settled at check-out. */
    public function paid(): static
    {
        return $this->state(fn () => [
            'payment_status' => 'paid',
            'payment_method' => 'cash',
        ]);
    }

    /** Billed because the guest never arrived. */
    public function noShow(): static
    {
        return $this->state(fn () => ['payment_status' => 'no_show']);
    }
}
