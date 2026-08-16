<?php

namespace Tests\Feature;

use App\Models\Billing;
use App\Models\Branch;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\TravelAgency;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * Front-desk operations: check-in, check-out, the 24-hour correction window,
 * travel agency block booking, and the branch scoping that keeps one branch's
 * clerks out of another's reservations.
 */
class ClerkOperationsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
    }

    public function test_clerk_dashboard_loads(): void
    {
        $branch = Branch::factory()->create();
        $clerk = User::factory()->clerk($branch)->create();

        $this->actingAs($clerk)
            ->get(route('clerk.dashboard', ['branch_id' => $branch->id]))
            ->assertStatus(200);
    }

    public function test_admin_dashboard_loads(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertStatus(200);
    }

    public function test_admin_can_register_staff(): void
    {
        $admin = User::factory()->admin()->create();
        $branch = Branch::factory()->create();

        $response = $this->actingAs($admin)->post('/admin/staff/register', [
            'name' => 'New Clerk',
            'email' => 'newclerk@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'clerk',
            'branch_id' => $branch->id,
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertDatabaseHas('users', [
            'email' => 'newclerk@example.com',
            'role' => 'clerk',
            'branch_id' => $branch->id,
        ]);

        Mail::assertSent(\App\Mail\RegistrationConfirmation::class);
    }

    /**
     * Check-in is where a physical room is finally assigned, and where the
     * folio is opened.
     */
    public function test_clerk_can_check_in_a_guest(): void
    {
        $branch = Branch::factory()->create();
        $clerk = User::factory()->clerk($branch)->create();
        $roomType = RoomType::factory()->create();

        $room = Room::factory()->create([
            'branch_id' => $branch->id,
            'room_type_id' => $roomType->id,
        ]);

        $reservation = Reservation::factory()->create([
            'branch_id' => $branch->id,
            'room_type_id' => $roomType->id,
            'status' => 'pending',
            'check_in_date' => Carbon::today(),
        ]);

        $response = $this->actingAs($clerk)->post("/clerk/check-in/{$reservation->id}", [
            'room_id' => $room->id,
            'credit_card_details' => '4242424242424242',
            'card_expiry' => '04/29',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('reservations', [
            'id' => $reservation->id,
            'status' => 'checked_in',
            'room_id' => $room->id,
        ]);

        // The room is now occupied and a folio exists.
        $this->assertSame('occupied', $room->fresh()->status);
        $this->assertDatabaseHas('billings', [
            'reservation_id' => $reservation->id,
            'payment_status' => 'pending',
        ]);
    }

    /**
     * The full card number must never be persisted — only the masked guarantee.
     */
    public function test_check_in_stores_only_a_masked_card_guarantee(): void
    {
        $branch = Branch::factory()->create();
        $clerk = User::factory()->clerk($branch)->create();
        $roomType = RoomType::factory()->create();

        $room = Room::factory()->create([
            'branch_id' => $branch->id,
            'room_type_id' => $roomType->id,
        ]);

        $reservation = Reservation::factory()->create([
            'branch_id' => $branch->id,
            'room_type_id' => $roomType->id,
            'status' => 'pending',
            'check_in_date' => Carbon::today(),
        ]);

        $this->actingAs($clerk)->post("/clerk/check-in/{$reservation->id}", [
            'room_id' => $room->id,
            'credit_card_details' => '4242424242424242',
            'card_expiry' => '04/29',
        ]);

        $stored = $reservation->fresh()->credit_card_details;

        $this->assertStringNotContainsString('4242424242424242', $stored);
        $this->assertStringContainsString('4242', $stored);   // last four kept
        $this->assertStringContainsString('04/29', $stored);
    }

    public function test_check_in_rejects_an_invalid_card_number(): void
    {
        $branch = Branch::factory()->create();
        $clerk = User::factory()->clerk($branch)->create();
        $roomType = RoomType::factory()->create();

        $room = Room::factory()->create([
            'branch_id' => $branch->id,
            'room_type_id' => $roomType->id,
        ]);

        $reservation = Reservation::factory()->create([
            'branch_id' => $branch->id,
            'room_type_id' => $roomType->id,
            'status' => 'pending',
            'check_in_date' => Carbon::today(),
        ]);

        $response = $this->actingAs($clerk)->post("/clerk/check-in/{$reservation->id}", [
            'room_id' => $room->id,
            'credit_card_details' => '4242424242424243',   // fails Luhn
            'card_expiry' => '04/29',
        ]);

        $response->assertSessionHasErrors('credit_card_details');
        $this->assertSame('pending', $reservation->fresh()->status);
    }

    public function test_clerk_can_check_out_with_billing(): void
    {
        $branch = Branch::factory()->create();
        $clerk = User::factory()->clerk($branch)->create();
        $guest = User::factory()->create();
        $roomType = RoomType::factory()->create(['price_per_night' => 100]);

        $room = Room::factory()->create([
            'branch_id' => $branch->id,
            'room_type_id' => $roomType->id,
            'status' => 'occupied',
        ]);

        $reservation = Reservation::factory()->create([
            'user_id' => $guest->id,
            'branch_id' => $branch->id,
            'room_type_id' => $roomType->id,
            'room_id' => $room->id,
            'status' => 'checked_in',
        ]);

        Billing::factory()->create([
            'reservation_id' => $reservation->id,
            'user_id' => $guest->id,
            'branch_id' => $branch->id,
        ]);

        $response = $this->actingAs($clerk)->post("/clerk/check-out/{$reservation->id}", [
            'payment_method' => 'cash',
            'restaurant_charges' => 50,
            'room_service_charges' => 0,
            'laundry_charges' => 0,
            'telephone_charges' => 0,
            'club_facility_charges' => 0,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('reservations', [
            'id' => $reservation->id,
            'status' => 'checked_out',
        ]);
        $this->assertDatabaseHas('billings', [
            'reservation_id' => $reservation->id,
            'payment_status' => 'paid',
            'payment_method' => 'cash',
            'restaurant_charges' => 50,
        ]);

        // The room returns to the available pool.
        $this->assertSame('available', $room->fresh()->status);

        Mail::assertSent(\App\Mail\PaymentConfirmation::class);
    }

    /**
     * A completed check-out stays amendable for 24 hours.
     */
    public function test_clerk_can_amend_a_recent_check_out(): void
    {
        $branch = Branch::factory()->create();
        $clerk = User::factory()->clerk($branch)->create();
        $guest = User::factory()->create();
        $roomType = RoomType::factory()->create(['price_per_night' => 100]);

        $room = Room::factory()->create([
            'branch_id' => $branch->id,
            'room_type_id' => $roomType->id,
        ]);

        $reservation = Reservation::factory()->create([
            'user_id' => $guest->id,
            'branch_id' => $branch->id,
            'room_type_id' => $roomType->id,
            'room_id' => $room->id,
            'status' => 'checked_out',
            'updated_at' => Carbon::now()->subHours(2),
        ]);

        Billing::factory()->paid()->create([
            'reservation_id' => $reservation->id,
            'user_id' => $guest->id,
            'branch_id' => $branch->id,
        ]);

        $response = $this->actingAs($clerk)->put("/clerk/edit-check-out/{$reservation->id}", [
            'payment_method' => 'cash',
            'payment_status' => 'paid',
            'restaurant_charges' => 75,
            'room_service_charges' => 25,
            'laundry_charges' => 0,
            'telephone_charges' => 0,
            'club_facility_charges' => 0,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('billings', [
            'reservation_id' => $reservation->id,
            'restaurant_charges' => 75,
            'room_service_charges' => 25,
        ]);
    }

    public function test_room_availability_page_loads(): void
    {
        $branch = Branch::factory()->create();
        $clerk = User::factory()->clerk($branch)->create();
        $roomType = RoomType::factory()->create();

        $room = Room::factory()->create([
            'branch_id' => $branch->id,
            'room_type_id' => $roomType->id,
        ]);

        $response = $this->actingAs($clerk)
            ->get(route('clerk.room-availability', ['branch_id' => $branch->id]));

        $response->assertStatus(200);
        $response->assertSee($room->room_number);
    }

    /**
     * Block booking: minimum three rooms in total, quotation split across every
     * room created, all inside one transaction.
     */
    public function test_travel_agency_block_booking_creates_a_reservation_per_room(): void
    {
        $branch = Branch::factory()->create();
        $clerk = User::factory()->clerk($branch)->create();
        $agency = TravelAgency::factory()->create();
        $roomType = RoomType::factory()->create(['max_occupants' => 2]);

        Room::factory()->count(5)->create([
            'branch_id' => $branch->id,
            'room_type_id' => $roomType->id,
        ]);

        $response = $this->actingAs($clerk)->post('/clerk/travel-agency', [
            'travel_agency_id' => $agency->id,
            'branch_id' => $branch->id,
            'discount_percentage' => 10,
            'quotation_amount' => 900,
            'room_types' => [
                $roomType->id => [
                    'selected' => 1,
                    'quantity' => 3,
                    'occupants' => 2,
                    'check_in_date' => Carbon::tomorrow()->toDateString(),
                    'check_out_date' => Carbon::tomorrow()->addDays(2)->toDateString(),
                ],
            ],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseCount('reservations', 3);
        $this->assertDatabaseCount('travel_agency_bookings', 3);
    }

    /**
     * Below the three-room minimum the whole booking is refused, and the
     * transaction leaves nothing behind.
     */
    public function test_travel_agency_block_booking_enforces_three_room_minimum(): void
    {
        $branch = Branch::factory()->create();
        $clerk = User::factory()->clerk($branch)->create();
        $agency = TravelAgency::factory()->create();
        $roomType = RoomType::factory()->create(['max_occupants' => 2]);

        Room::factory()->count(5)->create([
            'branch_id' => $branch->id,
            'room_type_id' => $roomType->id,
        ]);

        $response = $this->actingAs($clerk)->post('/clerk/travel-agency', [
            'travel_agency_id' => $agency->id,
            'branch_id' => $branch->id,
            'discount_percentage' => 10,
            'quotation_amount' => 400,
            'room_types' => [
                $roomType->id => [
                    'selected' => 1,
                    'quantity' => 2,           // under the minimum
                    'occupants' => 2,
                    'check_in_date' => Carbon::tomorrow()->toDateString(),
                    'check_out_date' => Carbon::tomorrow()->addDays(2)->toDateString(),
                ],
            ],
        ]);

        $response->assertSessionHasErrors();
        $this->assertDatabaseCount('reservations', 0);
    }

    /**
     * Branch scoping: a clerk cannot touch another branch's reservation.
     */
    public function test_clerk_cannot_check_in_another_branchs_reservation(): void
    {
        $ownBranch = Branch::factory()->create();
        $otherBranch = Branch::factory()->create();
        $clerk = User::factory()->clerk($ownBranch)->create();
        $roomType = RoomType::factory()->create();

        $room = Room::factory()->create([
            'branch_id' => $otherBranch->id,
            'room_type_id' => $roomType->id,
        ]);

        $reservation = Reservation::factory()->create([
            'branch_id' => $otherBranch->id,
            'room_type_id' => $roomType->id,
            'status' => 'pending',
        ]);

        $this->actingAs($clerk)
            ->post("/clerk/check-in/{$reservation->id}", ['room_id' => $room->id])
            ->assertNotFound();

        $this->assertSame('pending', $reservation->fresh()->status);
    }
}
