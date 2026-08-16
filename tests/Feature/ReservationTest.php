<?php

namespace Tests\Feature;

use App\Console\Commands\CancelNoCreditReservations;
use App\Models\Branch;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * Customer-facing booking, plus the business rules that make this a hotel
 * system rather than CRUD: the occupancy cap, the 4-weeks-becomes-1-month
 * suite rule, and the 19:00 no-guarantee auto-cancel.
 *
 * Note throughout: reservations.room_id stays NULL until check-in. Guests book
 * a room *type*; a clerk assigns the physical room at the desk.
 */
class ReservationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
    }

    /** A branch with enough inventory of one room type to satisfy a booking. */
    private function branchWithRooms(RoomType $roomType, int $count = 3): Branch
    {
        $branch = Branch::factory()->create();

        Room::factory()->count($count)->create([
            'branch_id' => $branch->id,
            'room_type_id' => $roomType->id,
        ]);

        return $branch;
    }

    public function test_customer_can_create_room_reservation(): void
    {
        $user = User::factory()->create();
        $roomType = RoomType::factory()->create(['max_occupants' => 2]);
        $branch = $this->branchWithRooms($roomType);

        $response = $this->actingAs($user)->post('/reservations', [
            'branch_id' => $branch->id,
            'room_type_id' => $roomType->id,
            'number_of_occupants' => 2,
            'check_in_date' => Carbon::tomorrow()->toDateString(),
            'check_out_date' => Carbon::tomorrow()->addDays(2)->toDateString(),
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('reservations', [
            'user_id' => $user->id,
            'branch_id' => $branch->id,
            'room_type_id' => $roomType->id,
            'status' => 'pending',
            'room_id' => null,
        ]);

        Mail::assertSent(\App\Mail\ReservationConfirmation::class);
    }

    public function test_customer_can_create_suite_reservation(): void
    {
        $user = User::factory()->create();
        $suite = RoomType::factory()->suite()->create(['max_occupants' => 4]);
        $branch = $this->branchWithRooms($suite);

        $checkIn = Carbon::tomorrow();

        $response = $this->actingAs($user)->post('/reservations', [
            'branch_id' => $branch->id,
            'room_type_id' => $suite->id,
            'number_of_occupants' => 2,
            'is_suite' => 1,
            'check_in_date' => $checkIn->toDateString(),
            'duration_type' => 'weeks',
            'duration_value' => 2,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('reservations', [
            'user_id' => $user->id,
            'room_type_id' => $suite->id,
            'duration_type' => 'weeks',
            'duration_value' => 2,
        ]);
    }

    /**
     * A suite booked for exactly 4 weeks is silently rewritten to 1 month and
     * billed at the cheaper monthly rate.
     */
    public function test_four_week_suite_booking_becomes_one_month(): void
    {
        $user = User::factory()->create();
        $suite = RoomType::factory()->suite()->create(['max_occupants' => 4]);
        $branch = $this->branchWithRooms($suite);

        $this->actingAs($user)->post('/reservations', [
            'branch_id' => $branch->id,
            'room_type_id' => $suite->id,
            'number_of_occupants' => 2,
            'is_suite' => 1,
            'check_in_date' => Carbon::tomorrow()->toDateString(),
            'duration_type' => 'weeks',
            'duration_value' => 4,
        ]);

        $this->assertDatabaseHas('reservations', [
            'user_id' => $user->id,
            'duration_type' => 'months',
            'duration_value' => 1,
        ]);
    }

    public function test_reservation_is_rejected_when_occupants_exceed_room_capacity(): void
    {
        $user = User::factory()->create();
        $roomType = RoomType::factory()->create(['max_occupants' => 2]);
        $branch = $this->branchWithRooms($roomType);

        $response = $this->actingAs($user)->post('/reservations', [
            'branch_id' => $branch->id,
            'room_type_id' => $roomType->id,
            'number_of_occupants' => 5,          // over the cap
            'check_in_date' => Carbon::tomorrow()->toDateString(),
            'check_out_date' => Carbon::tomorrow()->addDays(2)->toDateString(),
        ]);

        $response->assertSessionHasErrors();
        $this->assertDatabaseCount('reservations', 0);
    }

    /**
     * The core inventory guarantee: once every room of a type is spoken for on
     * overlapping dates, the next booking is refused.
     */
    public function test_reservation_is_rejected_when_no_rooms_remain(): void
    {
        $roomType = RoomType::factory()->create(['max_occupants' => 2]);
        $branch = $this->branchWithRooms($roomType, 1);   // exactly one room

        $checkIn = Carbon::tomorrow();
        $checkOut = $checkIn->copy()->addDays(3);

        Reservation::factory()->create([
            'branch_id' => $branch->id,
            'room_type_id' => $roomType->id,
            'check_in_date' => $checkIn,
            'check_out_date' => $checkOut,
            'status' => 'pending',
        ]);

        $response = $this->actingAs(User::factory()->create())->post('/reservations', [
            'branch_id' => $branch->id,
            'room_type_id' => $roomType->id,
            'number_of_occupants' => 1,
            'check_in_date' => $checkIn->copy()->addDay()->toDateString(),   // overlaps
            'check_out_date' => $checkOut->copy()->addDay()->toDateString(),
        ]);

        $response->assertSessionHasErrors('room_type_id');
        $this->assertDatabaseCount('reservations', 1);
    }

    /**
     * Correct hotel semantics: a stay ending on the day another begins does not
     * conflict, so the room can be re-sold for the departure date.
     */
    public function test_back_to_back_stays_do_not_conflict(): void
    {
        $roomType = RoomType::factory()->create(['max_occupants' => 2]);
        $branch = $this->branchWithRooms($roomType, 1);

        $checkIn = Carbon::tomorrow();
        $checkOut = $checkIn->copy()->addDays(2);

        Reservation::factory()->create([
            'branch_id' => $branch->id,
            'room_type_id' => $roomType->id,
            'check_in_date' => $checkIn,
            'check_out_date' => $checkOut,
            'status' => 'pending',
        ]);

        $response = $this->actingAs(User::factory()->create())->post('/reservations', [
            'branch_id' => $branch->id,
            'room_type_id' => $roomType->id,
            'number_of_occupants' => 1,
            'check_in_date' => $checkOut->toDateString(),        // starts as the other ends
            'check_out_date' => $checkOut->copy()->addDays(2)->toDateString(),
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseCount('reservations', 2);
    }

    public function test_reservation_management_dashboard_lists_own_reservations(): void
    {
        $user = User::factory()->create();
        $reservation = Reservation::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get('/reservations');

        $response->assertStatus(200);
        $response->assertSee($reservation->roomType->name);
    }

    public function test_customer_can_edit_reservation(): void
    {
        $user = User::factory()->create();
        $roomType = RoomType::factory()->create(['max_occupants' => 4]);
        $branch = $this->branchWithRooms($roomType);

        $reservation = Reservation::factory()->create([
            'user_id' => $user->id,
            'branch_id' => $branch->id,
            'room_type_id' => $roomType->id,
        ]);

        $newCheckIn = Carbon::tomorrow()->addDays(10);

        $response = $this->actingAs($user)->put("/reservations/{$reservation->id}", [
            'branch_id' => $branch->id,
            'room_type_id' => $roomType->id,
            'number_of_occupants' => 2,
            'check_in_date' => $newCheckIn->toDateString(),
            'check_out_date' => $newCheckIn->copy()->addDays(2)->toDateString(),
        ]);

        $response->assertRedirect();
        $this->assertSame(
            $newCheckIn->toDateString(),
            $reservation->fresh()->check_in_date->toDateString()
        );
    }

    public function test_clerk_can_create_reservation_for_a_guest(): void
    {
        $branch = Branch::factory()->create();
        $clerk = User::factory()->clerk($branch)->create();
        $guest = User::factory()->create();
        $roomType = RoomType::factory()->create(['max_occupants' => 2]);

        Room::factory()->count(2)->create([
            'branch_id' => $branch->id,
            'room_type_id' => $roomType->id,
        ]);

        $response = $this->actingAs($clerk)->post('/clerk/reservations', [
            'user_id' => $guest->id,
            'room_type_id' => $roomType->id,
            'number_of_occupants' => 2,
            'check_in_date' => Carbon::tomorrow()->toDateString(),
            'check_out_date' => Carbon::tomorrow()->addDays(2)->toDateString(),
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('reservations', [
            'user_id' => $guest->id,
            'branch_id' => $branch->id,
            'room_type_id' => $roomType->id,
        ]);
    }

    /**
     * Cancellation is a status change, not a delete — the row is retained so the
     * stay still appears in history and reporting.
     */
    public function test_customer_can_cancel_reservation(): void
    {
        $user = User::factory()->create();
        $reservation = Reservation::factory()->create([
            'user_id' => $user->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($user)->delete("/reservations/{$reservation->id}");

        $response->assertRedirect();
        $this->assertDatabaseHas('reservations', [
            'id' => $reservation->id,
            'status' => 'cancelled',
        ]);
    }

    /**
     * A cancelled reservation releases its room back into inventory.
     */
    public function test_cancelled_reservation_frees_inventory(): void
    {
        $roomType = RoomType::factory()->create(['max_occupants' => 2]);
        $branch = $this->branchWithRooms($roomType, 1);

        $checkIn = Carbon::tomorrow();
        $checkOut = $checkIn->copy()->addDays(3);

        Reservation::factory()->create([
            'branch_id' => $branch->id,
            'room_type_id' => $roomType->id,
            'check_in_date' => $checkIn,
            'check_out_date' => $checkOut,
            'status' => 'cancelled',        // released
        ]);

        $response = $this->actingAs(User::factory()->create())->post('/reservations', [
            'branch_id' => $branch->id,
            'room_type_id' => $roomType->id,
            'number_of_occupants' => 1,
            'check_in_date' => $checkIn->toDateString(),
            'check_out_date' => $checkOut->toDateString(),
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseCount('reservations', 2);
    }

    /**
     * The 19:00 job cancels same-day arrivals that never provided a card
     * guarantee, and leaves guaranteed ones alone.
     */
    public function test_auto_cancel_reservations_without_a_card_guarantee(): void
    {
        $withoutCard = Reservation::factory()->create([
            'check_in_date' => Carbon::today(),
            'status' => 'pending',
            'credit_card_details' => null,
        ]);

        $withCard = Reservation::factory()->create([
            'check_in_date' => Carbon::today(),
            'status' => 'pending',
            'credit_card_details' => '**** **** **** 4242 (exp 04/29)',
        ]);

        $this->artisan(CancelNoCreditReservations::class)->assertSuccessful();

        $this->assertSame('cancelled', $withoutCard->fresh()->status);
        $this->assertSame('pending', $withCard->fresh()->status);
    }
}
