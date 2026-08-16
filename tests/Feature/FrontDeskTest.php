<?php

namespace Tests\Feature;

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
 * The front desk's three operational lists — arrivals, departures, in-house —
 * plus the walk-in path, which books and checks in a guest in one step.
 */
class FrontDeskTest extends TestCase
{
    use RefreshDatabase;

    private Branch $branch;
    private User $clerk;
    private RoomType $roomType;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();

        $this->branch = Branch::factory()->create();
        $this->clerk = User::factory()->clerk($this->branch)->create();
        $this->roomType = RoomType::factory()->create([
            'max_occupants' => 2,
            'price_per_night' => 100,
        ]);
    }

    private function room(string $status = 'available'): Room
    {
        return Room::factory()->create([
            'branch_id' => $this->branch->id,
            'room_type_id' => $this->roomType->id,
            'status' => $status,
        ]);
    }

    public function test_front_desk_separates_arrivals_departures_and_in_house(): void
    {
        $arrivingGuest = User::factory()->create(['name' => 'Arriving Guest']);
        $departingGuest = User::factory()->create(['name' => 'Departing Guest']);
        $stayingGuest = User::factory()->create(['name' => 'Staying Guest']);

        // Due in today, not yet checked in.
        Reservation::factory()->create([
            'user_id' => $arrivingGuest->id,
            'branch_id' => $this->branch->id,
            'room_type_id' => $this->roomType->id,
            'status' => 'pending',
            'check_in_date' => Carbon::today(),
            'check_out_date' => Carbon::today()->addDays(2),
        ]);

        // In house and leaving today.
        Reservation::factory()->create([
            'user_id' => $departingGuest->id,
            'branch_id' => $this->branch->id,
            'room_type_id' => $this->roomType->id,
            'room_id' => $this->room('occupied')->id,
            'status' => 'checked_in',
            'check_in_date' => Carbon::today()->subDays(2),
            'check_out_date' => Carbon::today(),
        ]);

        // In house, leaving later.
        Reservation::factory()->create([
            'user_id' => $stayingGuest->id,
            'branch_id' => $this->branch->id,
            'room_type_id' => $this->roomType->id,
            'room_id' => $this->room('occupied')->id,
            'status' => 'checked_in',
            'check_in_date' => Carbon::today()->subDay(),
            'check_out_date' => Carbon::today()->addDays(3),
        ]);

        $response = $this->actingAs($this->clerk)->get(route('clerk.front-desk'));

        $response->assertStatus(200);
        $response->assertViewHas('arrivals', fn ($arrivals) => $arrivals->count() === 1);
        $response->assertViewHas('departures', fn ($departures) => $departures->count() === 1);
        // In-house is everyone checked in, including today's departure.
        $response->assertViewHas('inHouse', fn ($inHouse) => $inHouse->count() === 2);
    }

    public function test_front_desk_excludes_other_branches(): void
    {
        $otherBranch = Branch::factory()->create();

        Reservation::factory()->create([
            'branch_id' => $otherBranch->id,
            'room_type_id' => $this->roomType->id,
            'status' => 'pending',
            'check_in_date' => Carbon::today(),
        ]);

        $this->actingAs($this->clerk)
            ->get(route('clerk.front-desk'))
            ->assertViewHas('arrivals', fn ($arrivals) => $arrivals->isEmpty());
    }

    /**
     * A guest still checked in past their departure date is surfaced, because
     * their room is being counted as occupied.
     */
    public function test_front_desk_flags_overdue_departures(): void
    {
        Reservation::factory()->create([
            'branch_id' => $this->branch->id,
            'room_type_id' => $this->roomType->id,
            'room_id' => $this->room('occupied')->id,
            'status' => 'checked_in',
            'check_in_date' => Carbon::today()->subDays(5),
            'check_out_date' => Carbon::today()->subDays(2),   // should have gone
        ]);

        $this->actingAs($this->clerk)
            ->get(route('clerk.front-desk'))
            ->assertViewHas('overdue', fn ($overdue) => $overdue->count() === 1);
    }

    public function test_available_rooms_endpoint_lists_only_free_rooms_of_that_type(): void
    {
        $free = $this->room();
        $this->room('occupied');
        $this->room('maintenance');

        $response = $this->actingAs($this->clerk)
            ->getJson(route('clerk.available-rooms', ['roomType' => $this->roomType->id]));

        $response->assertStatus(200);
        $response->assertJsonCount(1);
        $response->assertJsonFragment(['room_number' => $free->room_number]);
    }

    public function test_walk_in_books_and_checks_in_immediately(): void
    {
        $guest = User::factory()->create();
        $room = $this->room();

        $response = $this->actingAs($this->clerk)->post(route('clerk.walk-in.store'), [
            'user_id' => $guest->id,
            'room_type_id' => $this->roomType->id,
            'room_id' => $room->id,
            'number_of_occupants' => 2,
            'check_out_date' => Carbon::tomorrow()->toDateString(),
        ]);

        $response->assertRedirect(route('clerk.front-desk', ['branch_id' => $this->branch->id]));

        $this->assertDatabaseHas('reservations', [
            'user_id' => $guest->id,
            'branch_id' => $this->branch->id,
            'room_id' => $room->id,
            'status' => 'checked_in',
        ]);

        // Room taken out of the pool and a folio opened.
        $this->assertSame('occupied', $room->fresh()->status);
        $this->assertDatabaseHas('billings', [
            'user_id' => $guest->id,
            'payment_status' => 'pending',
        ]);
    }

    public function test_walk_in_stores_only_a_masked_card_guarantee(): void
    {
        $guest = User::factory()->create();
        $room = $this->room();

        $this->actingAs($this->clerk)->post(route('clerk.walk-in.store'), [
            'user_id' => $guest->id,
            'room_type_id' => $this->roomType->id,
            'room_id' => $room->id,
            'number_of_occupants' => 1,
            'check_out_date' => Carbon::tomorrow()->toDateString(),
            'credit_card_details' => '4242424242424242',
            'card_expiry' => '04/29',
        ]);

        $stored = Reservation::where('user_id', $guest->id)->first()->credit_card_details;

        $this->assertStringNotContainsString('4242424242424242', $stored);
        $this->assertStringContainsString('4242', $stored);
        $this->assertStringContainsString('04/29', $stored);
    }

    public function test_walk_in_rejects_occupants_over_the_room_type_cap(): void
    {
        $guest = User::factory()->create();
        $room = $this->room();

        $response = $this->actingAs($this->clerk)->post(route('clerk.walk-in.store'), [
            'user_id' => $guest->id,
            'room_type_id' => $this->roomType->id,
            'room_id' => $room->id,
            'number_of_occupants' => 5,          // cap is 2
            'check_out_date' => Carbon::tomorrow()->toDateString(),
        ]);

        $response->assertSessionHasErrors('number_of_occupants');
        $this->assertDatabaseCount('reservations', 0);
        $this->assertSame('available', $room->fresh()->status);
    }

    public function test_walk_in_rejects_a_room_that_is_not_free(): void
    {
        $guest = User::factory()->create();
        $occupied = $this->room('occupied');

        $response = $this->actingAs($this->clerk)->post(route('clerk.walk-in.store'), [
            'user_id' => $guest->id,
            'room_type_id' => $this->roomType->id,
            'room_id' => $occupied->id,
            'number_of_occupants' => 1,
            'check_out_date' => Carbon::tomorrow()->toDateString(),
        ]);

        $response->assertSessionHasErrors('room_id');
        $this->assertDatabaseCount('reservations', 0);
    }

    public function test_walk_in_rejects_another_branchs_room(): void
    {
        $guest = User::factory()->create();
        $otherBranch = Branch::factory()->create();

        $foreignRoom = Room::factory()->create([
            'branch_id' => $otherBranch->id,
            'room_type_id' => $this->roomType->id,
        ]);

        $response = $this->actingAs($this->clerk)->post(route('clerk.walk-in.store'), [
            'user_id' => $guest->id,
            'room_type_id' => $this->roomType->id,
            'room_id' => $foreignRoom->id,
            'number_of_occupants' => 1,
            'check_out_date' => Carbon::tomorrow()->toDateString(),
        ]);

        $response->assertSessionHasErrors('room_id');
        $this->assertDatabaseCount('reservations', 0);
    }

    /**
     * A free room today is not enough — the stay must also fit around
     * reservations already booked across those dates.
     */
    public function test_walk_in_respects_future_reservations(): void
    {
        $guest = User::factory()->create();
        $room = $this->room();   // the branch's only room of this type

        Reservation::factory()->create([
            'branch_id' => $this->branch->id,
            'room_type_id' => $this->roomType->id,
            'status' => 'confirmed',
            'check_in_date' => Carbon::today(),
            'check_out_date' => Carbon::today()->addDays(4),
        ]);

        $response = $this->actingAs($this->clerk)->post(route('clerk.walk-in.store'), [
            'user_id' => $guest->id,
            'room_type_id' => $this->roomType->id,
            'room_id' => $room->id,
            'number_of_occupants' => 1,
            'check_out_date' => Carbon::today()->addDays(2)->toDateString(),
        ]);

        $response->assertSessionHasErrors('room_type_id');
        $this->assertSame('available', $room->fresh()->status);
    }

    public function test_walk_in_rejects_a_suite(): void
    {
        $guest = User::factory()->create();
        $suite = RoomType::factory()->suite()->create(['max_occupants' => 4]);

        $suiteRoom = Room::factory()->create([
            'branch_id' => $this->branch->id,
            'room_type_id' => $suite->id,
        ]);

        $response = $this->actingAs($this->clerk)->post(route('clerk.walk-in.store'), [
            'user_id' => $guest->id,
            'room_type_id' => $suite->id,
            'room_id' => $suiteRoom->id,
            'number_of_occupants' => 2,
            'check_out_date' => Carbon::tomorrow()->toDateString(),
        ]);

        $response->assertSessionHasErrors('room_type_id');
        $this->assertDatabaseCount('reservations', 0);
    }
}
